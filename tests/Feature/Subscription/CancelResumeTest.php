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
});

test('Freeプランのユーザーが解約してもStripeには問い合わせない', function () {
    $user = User::factory()->create();
    $fake = fakeStripeHttp([]);

    $this->actingAs($user)->post(route('subscription.cancel'))->assertRedirect();

    expect($fake->requestCount())->toBe(0);
});

test('有料契約中のユーザーは解約すると期間終了時キャンセル予約になる', function () {
    $user = User::factory()->create();
    $user->subscription->update([
        'plan_id' => $this->pro->id,
        'stripe_subscription_id' => 'sub_123',
        'status' => Subscription::STATUS_ACTIVE,
    ]);

    fakeStripeHttp([['id' => 'sub_123', 'cancel_at_period_end' => true]]);

    $this->actingAs($user)->post(route('subscription.cancel'))->assertRedirect();

    expect($user->subscription->fresh()->cancel_at_period_end)->toBeTrue();
});

test('解約予約を取り消すとcancel_at_period_endがfalseに戻る', function () {
    $user = User::factory()->create();
    $user->subscription->update([
        'plan_id' => $this->pro->id,
        'stripe_subscription_id' => 'sub_123',
        'status' => Subscription::STATUS_ACTIVE,
        'cancel_at_period_end' => true,
    ]);

    fakeStripeHttp([['id' => 'sub_123', 'cancel_at_period_end' => false]]);

    $this->actingAs($user)->post(route('subscription.resume'))->assertRedirect();

    expect($user->subscription->fresh()->cancel_at_period_end)->toBeFalse();
});
