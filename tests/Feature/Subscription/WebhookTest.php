<?php

use App\Models\Plan;
use App\Models\StripeWebhookEvent;
use App\Models\Subscription;
use App\Models\User;
use Tests\TestCase;

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

    $this->user = User::factory()->create();
});

function postStripeWebhook(TestCase $testCase, string $payload, string $signature)
{
    // ->call() は withHeaders() で登録したヘッダーを反映しないため、
    // SERVER配列に直接HTTP_*形式で積む。
    return $testCase->call('POST', route('stripe.webhook'), server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => $signature,
    ], content: $payload);
}

test('署名が不正なWebhookは400で拒否され、何も記録されない', function () {
    $response = postStripeWebhook($this, '{"id":"evt_bad"}', 't=1,v1=invalid');

    $response->assertStatus(400);
    expect(StripeWebhookEvent::count())->toBe(0);
});

test('checkout.session.completedでプランがStripeの契約情報どおりに同期される', function () {
    fakeStripeHttp([
        [
            'id' => 'sub_123',
            'status' => 'trialing',
            'trial_end' => now()->addDays(14)->timestamp,
            'cancel_at_period_end' => false,
            'items' => [
                'data' => [[
                    'price' => ['id' => 'price_pro_test'],
                    'current_period_end' => now()->addMonth()->timestamp,
                ]],
            ],
        ],
    ]);

    [$payload, $signature] = signedStripeEvent('evt_1', 'checkout.session.completed', [
        'customer' => 'cus_123',
        'subscription' => 'sub_123',
        'client_reference_id' => (string) $this->user->id,
        'metadata' => ['user_id' => (string) $this->user->id],
    ]);

    $response = postStripeWebhook($this, $payload, $signature);

    $response->assertNoContent();

    $this->user->refresh();
    expect($this->user->stripe_customer_id)->toBe('cus_123');
    expect($this->user->subscription->plan_id)->toBe($this->pro->id);
    expect($this->user->subscription->stripe_subscription_id)->toBe('sub_123');
    expect($this->user->subscription->status)->toBe('trialing');

    expect(StripeWebhookEvent::first()->status)->toBe(StripeWebhookEvent::STATUS_PROCESSED);
});

test('同じイベントが再送されても二重処理されない(冪等性)', function () {
    $fake = fakeStripeHttp([
        [
            'id' => 'sub_123',
            'status' => 'active',
            'trial_end' => null,
            'cancel_at_period_end' => false,
            'items' => ['data' => [[
                'price' => ['id' => 'price_pro_test'],
                'current_period_end' => now()->addMonth()->timestamp,
            ]]],
        ],
    ]);

    [$payload, $signature] = signedStripeEvent('evt_duplicate', 'checkout.session.completed', [
        'customer' => 'cus_123',
        'subscription' => 'sub_123',
        'client_reference_id' => (string) $this->user->id,
        'metadata' => ['user_id' => (string) $this->user->id],
    ]);

    postStripeWebhook($this, $payload, $signature)->assertNoContent();
    expect($fake->requestCount())->toBe(1);
    expect(StripeWebhookEvent::count())->toBe(1);

    // 全く同じイベント(同一ID・同一署名)がStripeから再送されてきたケース
    postStripeWebhook($this, $payload, $signature)->assertNoContent();

    // Stripeへの追加リクエストは発生せず、レコードも増えない
    expect($fake->requestCount())->toBe(1);
    expect(StripeWebhookEvent::count())->toBe(1);
});

test('customer.subscription.updatedでプラン変更とキャンセル予約が同期される', function () {
    $this->user->subscription->update([
        'plan_id' => $this->pro->id,
        'stripe_subscription_id' => 'sub_123',
        'status' => Subscription::STATUS_ACTIVE,
    ]);

    [$payload, $signature] = signedStripeEvent('evt_2', 'customer.subscription.updated', [
        'id' => 'sub_123',
        'customer' => 'cus_123',
        'status' => 'active',
        'trial_end' => null,
        'cancel_at_period_end' => true,
        'items' => ['data' => [[
            'price' => ['id' => 'price_enterprise_test'],
            'current_period_end' => now()->addMonth()->timestamp,
        ]]],
    ]);

    postStripeWebhook($this, $payload, $signature)->assertNoContent();

    $this->user->subscription->refresh();
    expect($this->user->subscription->plan_id)->toBe($this->enterprise->id);
    expect($this->user->subscription->cancel_at_period_end)->toBeTrue();
});

test('customer.subscription.deletedでFreeプランに戻る', function () {
    $this->user->subscription->update([
        'plan_id' => $this->pro->id,
        'stripe_subscription_id' => 'sub_123',
        'status' => Subscription::STATUS_ACTIVE,
    ]);

    [$payload, $signature] = signedStripeEvent('evt_3', 'customer.subscription.deleted', [
        'id' => 'sub_123',
        'customer' => 'cus_123',
    ]);

    postStripeWebhook($this, $payload, $signature)->assertNoContent();

    $this->user->subscription->refresh();
    expect($this->user->subscription->plan_id)->toBe($this->free->id);
    expect($this->user->subscription->stripe_subscription_id)->toBeNull();
});

test('invoice.payment_failedで支払い遅延ステータスになる', function () {
    $this->user->subscription->update([
        'plan_id' => $this->pro->id,
        'stripe_subscription_id' => 'sub_123',
        'status' => Subscription::STATUS_ACTIVE,
    ]);

    [$payload, $signature] = signedStripeEvent('evt_4', 'invoice.payment_failed', [
        'subscription' => 'sub_123',
    ]);

    postStripeWebhook($this, $payload, $signature)->assertNoContent();

    $this->user->subscription->refresh();
    expect($this->user->subscription->status)->toBe(Subscription::STATUS_PAST_DUE);
});
