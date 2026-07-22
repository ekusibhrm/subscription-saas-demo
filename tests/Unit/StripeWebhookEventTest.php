<?php

use App\Models\StripeWebhookEvent;

test('findOrCreateReceivedは同じイベントIDに対して同じレコードを返す', function () {
    $first = StripeWebhookEvent::findOrCreateReceived('evt_1', 'checkout.session.completed', ['id' => 'evt_1']);
    $second = StripeWebhookEvent::findOrCreateReceived('evt_1', 'checkout.session.completed', ['id' => 'evt_1']);

    expect(StripeWebhookEvent::count())->toBe(1);
    expect($second->id)->toBe($first->id);
    expect($first->status)->toBe(StripeWebhookEvent::STATUS_RECEIVED);
});

test('処理成功後はalreadyProcessedがtrueになり、失敗のままなら再処理対象になる', function () {
    $processed = StripeWebhookEvent::findOrCreateReceived('evt_ok', 'invoice.payment_failed', []);
    $processed->markProcessed();
    expect($processed->fresh()->alreadyProcessed())->toBeTrue();

    $failed = StripeWebhookEvent::findOrCreateReceived('evt_fail', 'invoice.payment_failed', []);
    $failed->markFailed();
    expect($failed->fresh()->alreadyProcessed())->toBeFalse();

    // 失敗イベントがStripeから再送された場合、同じレコードが返り再処理できる
    $retried = StripeWebhookEvent::findOrCreateReceived('evt_fail', 'invoice.payment_failed', []);
    expect($retried->id)->toBe($failed->id);
    expect($retried->alreadyProcessed())->toBeFalse();
});
