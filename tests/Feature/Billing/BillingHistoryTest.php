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

test('Stripe顧客情報がないユーザーは請求履歴が空で表示される', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('billing.index'));

    $response->assertOk();
    $response->assertSee('請求履歴はまだありません');
});

test('Stripeの請求書一覧がそのまま表示される', function () {
    $user = User::factory()->create(['stripe_customer_id' => 'cus_123']);

    fakeStripeHttp([
        [
            'object' => 'list',
            'data' => [
                [
                    'id' => 'in_123',
                    'number' => 'INV-001',
                    'created' => now()->timestamp,
                    'amount_paid' => 1500,
                    'currency' => 'jpy',
                    'status' => 'paid',
                    'hosted_invoice_url' => 'https://invoice.stripe.com/i/in_123',
                    'invoice_pdf' => 'https://invoice.stripe.com/i/in_123/pdf',
                ],
            ],
        ],
    ]);

    $response = $this->actingAs($user)->get(route('billing.index'));

    $response->assertOk();
    $response->assertSee('INV-001');
    $response->assertSee('¥1,500');
});
