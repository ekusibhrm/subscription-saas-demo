<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

#[Fillable(['stripe_event_id', 'type', 'payload', 'status', 'processed_at'])]
class StripeWebhookEvent extends Model
{
    public const UPDATED_AT = null;

    public const STATUS_RECEIVED = 'received';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_FAILED = 'failed';

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * イベントIDでレコードを探し、なければ作成する。
     *
     * stripe_event_id の UNIQUE 制約により、同時に同じイベントが並行して届いても
     * INSERTに成功するのはどちらか一方だけになる(負けた側は例外を捕捉して再取得する)。
     * これにより「未処理 / 処理済み / 失敗して再送されてきた」を区別でき、
     * Stripeが自動リトライしてくる失敗イベントは再処理できる一方、
     * 一度成功したイベントの二重処理だけを確実に防げる。
     */
    public static function findOrCreateReceived(string $stripeEventId, string $type, array $payload): self
    {
        $event = self::where('stripe_event_id', $stripeEventId)->first();

        if ($event !== null) {
            return $event;
        }

        try {
            return self::create([
                'stripe_event_id' => $stripeEventId,
                'type' => $type,
                'payload' => $payload,
                'status' => self::STATUS_RECEIVED,
            ]);
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                return self::where('stripe_event_id', $stripeEventId)->firstOrFail();
            }

            throw $e;
        }
    }

    public function alreadyProcessed(): bool
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    public function markProcessed(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'processed_at' => now(),
        ]);
    }

    public function markFailed(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'processed_at' => now(),
        ]);
    }
}
