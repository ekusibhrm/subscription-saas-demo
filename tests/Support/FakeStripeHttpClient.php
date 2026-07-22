<?php

namespace Tests\Support;

use Stripe\HttpClient\ClientInterface;

/**
 * Stripe公式SDKのHTTPクライアント差し替え機構(\Stripe\ApiRequestor::setHttpClient())を使い、
 * テスト中は実際にStripeへ通信せず、あらかじめ用意したレスポンスを順番に返す。
 */
class FakeStripeHttpClient implements ClientInterface
{
    /** @var array<int, array{method: string, url: string, params: array}> */
    public array $requests = [];

    private int $cursor = 0;

    /**
     * @param  array<int, array<string, mixed>>  $responses  呼ばれた順に返すレスポンスボディ
     */
    public function __construct(private array $responses) {}

    public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
    {
        $this->requests[] = [
            'method' => $method,
            'url' => $absUrl,
            'params' => $params,
        ];

        $body = $this->responses[$this->cursor] ?? ['id' => 'fake_id'];
        $this->cursor++;

        return [json_encode($body), 200, []];
    }

    public function requestCount(): int
    {
        return count($this->requests);
    }
}
