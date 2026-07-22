<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

beforeEach(function () {
    $this->free = Plan::create([
        'slug' => 'free', 'name' => 'Free', 'stripe_price_id' => null,
        'price_jpy' => 0, 'trial_days' => 0,
        'features' => ['document_limit' => 3, 'attachments' => false, 'priority_support' => false],
        'sort_order' => 1,
    ]);

    $this->pro = Plan::create([
        'slug' => 'pro', 'name' => 'Pro', 'stripe_price_id' => 'price_pro_test',
        'price_jpy' => 1500, 'trial_days' => 14,
        'features' => ['document_limit' => null, 'attachments' => true, 'priority_support' => false],
        'sort_order' => 2,
    ]);

    $this->enterprise = Plan::create([
        'slug' => 'enterprise', 'name' => 'Enterprise', 'stripe_price_id' => 'price_enterprise_test',
        'price_jpy' => 5000, 'trial_days' => 14,
        'features' => ['document_limit' => null, 'attachments' => true, 'priority_support' => true],
        'sort_order' => 3,
    ]);
});

test('新規ユーザーは登録時に自動でFreeプランになる', function () {
    $user = User::factory()->create();

    expect($user->subscription)->not->toBeNull();
    expect($user->currentPlan()->slug)->toBe('free');
});

test('Proプランを選択すると初回はStripe Checkoutセッションが作成されリダイレクトされる', function () {
    $user = User::factory()->create();

    $fake = fakeStripeHttp([
        ['id' => 'cus_123'], // customers->create
        ['id' => 'cs_test_123', 'url' => 'https://checkout.stripe.com/pay/cs_test_123'], // checkout session
    ]);

    $response = $this->actingAs($user)->post(route('subscription.subscribe', $this->pro));

    $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');
    expect($fake->requestCount())->toBe(2);

    $sessionRequestParams = $fake->requests[1]['params'];
    expect($sessionRequestParams['subscription_data']['trial_period_days'])->toBe(14);
    expect($sessionRequestParams['line_items'][0]['price'])->toBe('price_pro_test');

    $user->refresh();
    expect($user->stripe_customer_id)->toBe('cus_123');
});

test('既にPro契約中のユーザーがEnterpriseに変更すると新規Checkoutを作らず既存契約を更新する', function () {
    $user = User::factory()->create();
    $user->update(['stripe_customer_id' => 'cus_existing']);
    $user->subscription->update([
        'plan_id' => $this->pro->id,
        'stripe_subscription_id' => 'sub_existing',
        'status' => Subscription::STATUS_ACTIVE,
    ]);

    $fake = fakeStripeHttp([
        [
            'id' => 'sub_existing',
            'items' => ['data' => [['id' => 'si_123', 'price' => ['id' => 'price_pro_test']]]],
        ], // subscriptions->retrieve
        ['id' => 'sub_existing'], // subscriptions->update
    ]);

    $response = $this->actingAs($user)->post(route('subscription.subscribe', $this->enterprise));

    $response->assertRedirect(route('plans.index'));
    expect($fake->requestCount())->toBe(2);

    $user->subscription->refresh();
    expect($user->subscription->plan_id)->toBe($this->enterprise->id);
});

test('有料プランからFreeを選ぶと即時解約ではなく期間終了時の解約予約になる', function () {
    $user = User::factory()->create();
    $user->subscription->update([
        'plan_id' => $this->pro->id,
        'stripe_subscription_id' => 'sub_existing',
        'status' => Subscription::STATUS_ACTIVE,
    ]);

    $fake = fakeStripeHttp([
        ['id' => 'sub_existing', 'cancel_at_period_end' => true],
    ]);

    $response = $this->actingAs($user)->post(route('subscription.subscribe', $this->free));

    $response->assertRedirect(route('plans.index'));
    expect($fake->requestCount())->toBe(1);

    $user->subscription->refresh();
    expect($user->subscription->cancel_at_period_end)->toBeTrue();
    expect($user->subscription->plan_id)->toBe($this->pro->id);
});
