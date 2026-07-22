<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    public function __construct(private readonly StripeClient $stripe) {}

    /**
     * プラン選択ボタンのエントリーポイント。
     * Free選択 / 新規Checkout / 既存契約のプラン変更(Pro⇔Enterprise)を振り分ける。
     */
    public function subscribe(Request $request, Plan $plan): RedirectResponse
    {
        $user = $request->user();
        $current = $user->subscription;

        if ($plan->isFree()) {
            return $this->downgradeToFree($user, $current);
        }

        if ($current !== null && $current->stripe_subscription_id !== null && $current->isActive()) {
            return $this->swapPlan($current, $plan);
        }

        return $this->startCheckout($user, $plan);
    }

    private function startCheckout(User $user, Plan $plan): RedirectResponse
    {
        if ($user->stripe_customer_id === null) {
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => ['user_id' => $user->id],
            ]);

            $user->update(['stripe_customer_id' => $customer->id]);
        }

        $subscriptionData = [];
        if ($plan->trial_days > 0) {
            $subscriptionData['trial_period_days'] = $plan->trial_days;
        }

        $session = $this->stripe->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $user->stripe_customer_id,
            'line_items' => [
                ['price' => $plan->stripe_price_id, 'quantity' => 1],
            ],
            'subscription_data' => $subscriptionData,
            'client_reference_id' => (string) $user->id,
            'metadata' => ['user_id' => $user->id, 'plan_id' => $plan->id],
            'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancelled'),
        ]);

        return redirect()->away($session->url);
    }

    /**
     * 既にPro/Enterpriseで契約中のユーザーがもう一方のプランへ変更する場合は、
     * 新しいCheckoutを作らず既存のStripe Subscriptionのアイテムを差し替える(日割り計算はStripeに任せる)。
     */
    private function swapPlan(Subscription $subscription, Plan $newPlan): RedirectResponse
    {
        $stripeSubscription = $this->stripe->subscriptions->retrieve($subscription->stripe_subscription_id);
        $itemId = $stripeSubscription->items->data[0]->id;

        $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
            'items' => [['id' => $itemId, 'price' => $newPlan->stripe_price_id]],
            'proration_behavior' => 'create_prorations',
        ]);

        // 正式な反映は customer.subscription.updated Webhook で行うが、
        // 画面へ即時反映させるため楽観的にも更新しておく。
        $subscription->update(['plan_id' => $newPlan->id]);

        return redirect()->route('plans.index')->with('status', "{$newPlan->name}プランに変更しました。");
    }

    private function downgradeToFree(User $user, ?Subscription $subscription): RedirectResponse
    {
        if ($subscription === null || $subscription->stripe_subscription_id === null) {
            return redirect()->route('plans.index')->with('status', '既にFreeプランです。');
        }

        $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);

        $subscription->update(['cancel_at_period_end' => true]);

        return redirect()->route('plans.index')
            ->with('status', '現在の契約期間の終了時にFreeプランへ移行します。');
    }

    public function success(Request $request): View
    {
        return view('checkout.success');
    }

    public function cancelled(Request $request): View
    {
        return view('checkout.cancelled');
    }

    /**
     * 契約期間終了時に解約されるようスケジュールする(即時解約はしない)。
     */
    public function cancel(Request $request): RedirectResponse
    {
        $subscription = $request->user()->subscription;

        if ($subscription === null || $subscription->stripe_subscription_id === null) {
            return back()->with('status', 'Freeプランは解約の必要がありません。');
        }

        $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);

        $subscription->update(['cancel_at_period_end' => true]);

        return back()->with('status', '解約を受け付けました。契約期間終了までは引き続きご利用いただけます。');
    }

    public function resume(Request $request): RedirectResponse
    {
        $subscription = $request->user()->subscription;

        if ($subscription === null || $subscription->stripe_subscription_id === null || ! $subscription->cancel_at_period_end) {
            return back()->with('status', '解約予定の契約がありません。');
        }

        $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
            'cancel_at_period_end' => false,
        ]);

        $subscription->update(['cancel_at_period_end' => false]);

        return back()->with('status', '解約を取り消しました。');
    }
}
