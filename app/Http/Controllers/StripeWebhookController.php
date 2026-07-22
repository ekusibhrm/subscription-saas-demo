<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\StripeWebhookEvent;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(private readonly StripeClient $stripe) {}

    public function handle(Request $request): Response
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                (string) config('services.stripe.webhook_secret'),
            );
        } catch (SignatureVerificationException|\UnexpectedValueException $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        // stripe_event_id のUNIQUE制約を使った冪等性チェック。
        // 既に「処理済み」ならここで即座に打ち切り、二重にプラン変更等を行わない。
        $record = StripeWebhookEvent::findOrCreateReceived($event->id, $event->type, $event->toArray());

        if ($record->alreadyProcessed()) {
            return response()->noContent();
        }

        try {
            $this->dispatch($event);
            $record->markProcessed();
        } catch (\Throwable $e) {
            $record->markFailed();

            Log::error('Stripe webhook processing failed', [
                'event_id' => $event->id,
                'type' => $event->type,
                'error' => $e->getMessage(),
            ]);

            // 5xxを返すとStripe側が自動リトライしてくれる。次回は同じイベントIDで
            // 届くが、このレコードはfailedのままなので再処理の対象になる。
            return response('Webhook handling failed', 500);
        }

        return response()->noContent();
    }

    private function dispatch(Event $event): void
    {
        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event),
            'invoice.payment_succeeded' => $this->handleInvoicePaymentSucceeded($event),
            default => null,
        };
    }

    private function handleCheckoutCompleted(Event $event): void
    {
        $session = $event->data->object;

        $userId = $session->metadata->user_id ?? $session->client_reference_id ?? null;

        if ($userId === null || empty($session->subscription)) {
            return;
        }

        $user = User::find($userId);

        if ($user === null) {
            return;
        }

        if ($user->stripe_customer_id !== $session->customer) {
            $user->update(['stripe_customer_id' => $session->customer]);
        }

        $stripeSubscription = $this->stripe->subscriptions->retrieve($session->subscription);

        $this->syncSubscription($user, $stripeSubscription);
    }

    private function handleSubscriptionUpdated(Event $event): void
    {
        $stripeSubscription = $event->data->object;
        $user = $this->resolveUser($stripeSubscription->customer, $stripeSubscription->id);

        if ($user === null) {
            return;
        }

        $this->syncSubscription($user, $stripeSubscription);
    }

    private function handleSubscriptionDeleted(Event $event): void
    {
        $stripeSubscription = $event->data->object;
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription === null) {
            return;
        }

        $freePlan = Plan::where('slug', 'free')->first();

        $subscription->update([
            'plan_id' => $freePlan?->id ?? $subscription->plan_id,
            'stripe_subscription_id' => null,
            'status' => Subscription::STATUS_ACTIVE,
            'trial_ends_at' => null,
            'current_period_end' => null,
            'cancel_at_period_end' => false,
        ]);
    }

    private function handleInvoicePaymentFailed(Event $event): void
    {
        $invoice = $event->data->object;

        if (empty($invoice->subscription)) {
            return;
        }

        Subscription::where('stripe_subscription_id', $invoice->subscription)
            ->update(['status' => Subscription::STATUS_PAST_DUE]);
    }

    private function handleInvoicePaymentSucceeded(Event $event): void
    {
        $invoice = $event->data->object;

        if (empty($invoice->subscription)) {
            return;
        }

        $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

        if ($subscription === null) {
            return;
        }

        $stripeSubscription = $this->stripe->subscriptions->retrieve($invoice->subscription);

        $this->syncSubscription($subscription->user, $stripeSubscription);
    }

    private function resolveUser(string $customerId, string $subscriptionId): ?User
    {
        $subscription = Subscription::where('stripe_subscription_id', $subscriptionId)->first();

        if ($subscription !== null) {
            return $subscription->user;
        }

        return User::where('stripe_customer_id', $customerId)->first();
    }

    /**
     * StripeのSubscriptionオブジェクトをローカルの subscriptions テーブルへ反映する。
     * checkout完了・更新・支払い成功のいずれからも呼ばれる共通の同期処理。
     */
    private function syncSubscription(User $user, $stripeSubscription): void
    {
        $priceId = $stripeSubscription->items->data[0]->price->id ?? null;
        $plan = Plan::where('stripe_price_id', $priceId)->first();

        if ($plan === null) {
            Log::warning('Unknown Stripe price id in webhook', ['price_id' => $priceId]);

            return;
        }

        $item = $stripeSubscription->items->data[0];
        $periodEnd = $item->current_period_end ?? $stripeSubscription->current_period_end ?? null;

        Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan->id,
                'stripe_subscription_id' => $stripeSubscription->id,
                'status' => $stripeSubscription->status,
                'trial_ends_at' => $stripeSubscription->trial_end
                    ? Carbon::createFromTimestamp($stripeSubscription->trial_end)
                    : null,
                'current_period_end' => $periodEnd
                    ? Carbon::createFromTimestamp($periodEnd)
                    : null,
                'cancel_at_period_end' => (bool) $stripeSubscription->cancel_at_period_end,
            ]
        );
    }
}
