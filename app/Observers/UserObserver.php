<?php

namespace App\Observers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class UserObserver
{
    /**
     * 新規登録ユーザーは全員Freeプランの契約状態からスタートする。
     */
    public function created(User $user): void
    {
        $freePlan = Plan::where('slug', 'free')->first();

        if ($freePlan === null) {
            return;
        }

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $freePlan->id,
            'status' => Subscription::STATUS_ACTIVE,
        ]);
    }
}
