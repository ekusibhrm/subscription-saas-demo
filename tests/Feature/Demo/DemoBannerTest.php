<?php

use App\Models\Plan;
use App\Models\User;

beforeEach(function () {
    Plan::create([
        'slug' => 'free', 'name' => 'Free', 'stripe_price_id' => null,
        'price_jpy' => 0, 'trial_days' => 0,
        'features' => ['document_limit' => 3, 'attachments' => false, 'priority_support' => false],
        'sort_order' => 1,
    ]);
});

test('トップページにデモ環境であることとテストカード番号が表示される', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('これはデモ環境です');
    $response->assertSee('4242 4242 4242 4242');
});

test('ログイン後のダッシュボードにもデモバナーが表示される', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('これはデモ環境です');
    $response->assertSee('4242 4242 4242 4242');
});
