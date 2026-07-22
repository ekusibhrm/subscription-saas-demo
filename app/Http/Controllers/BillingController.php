<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Stripe\StripeClient;

class BillingController extends Controller
{
    public function __construct(private readonly StripeClient $stripe) {}

    /**
     * 請求履歴・領収書はローカルに複製せず、都度Stripe側から取得して表示する。
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $invoices = new Collection;

        if ($user->stripe_customer_id !== null) {
            $invoices = collect($this->stripe->invoices->all([
                'customer' => $user->stripe_customer_id,
                'limit' => 20,
            ])->data);
        }

        return view('billing.index', [
            'invoices' => $invoices,
        ]);
    }
}
