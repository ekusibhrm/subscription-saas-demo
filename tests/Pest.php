<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\ApiRequestor;
use Tests\Support\FakeStripeHttpClient;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->afterEach(function () {
        ApiRequestor::setHttpClient(null);
    })
    ->in('Feature', 'Unit');

/**
 * Stripe SDKの通信をフェイクし、渡した順にレスポンスを返すクライアントに差し替える。
 *
 * @param  array<int, array<string, mixed>>  $responses
 */
function fakeStripeHttp(array $responses): FakeStripeHttpClient
{
    $client = new FakeStripeHttpClient($responses);
    ApiRequestor::setHttpClient($client);

    return $client;
}

/**
 * Stripe Webhookの署名付きペイロードを生成する(Stripe\WebhookSignatureと同じアルゴリズム)。
 *
 * @return array{0: string, 1: string} [JSONペイロード, Stripe-Signatureヘッダー]
 */
function signedStripeEvent(string $eventId, string $type, array $object, ?string $secret = null): array
{
    $secret ??= config('services.stripe.webhook_secret');

    $payload = json_encode([
        'id' => $eventId,
        'object' => 'event',
        'type' => $type,
        'data' => ['object' => $object],
    ]);

    $timestamp = time();
    $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);
    $header = "t={$timestamp},v1={$signature}";

    return [$payload, $header];
}
