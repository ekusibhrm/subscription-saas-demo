<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'name', 'stripe_price_id', 'price_jpy', 'interval', 'trial_days', 'features', 'sort_order'])]
class Plan extends Model
{
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price_jpy' => 'integer',
            'trial_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isFree(): bool
    {
        return $this->stripe_price_id === null;
    }

    public function documentLimit(): ?int
    {
        // null = 無制限
        return $this->features['document_limit'] ?? null;
    }

    public function allowsAttachments(): bool
    {
        return (bool) ($this->features['attachments'] ?? false);
    }

    public function hasPrioritySupport(): bool
    {
        return (bool) ($this->features['priority_support'] ?? false);
    }
}
