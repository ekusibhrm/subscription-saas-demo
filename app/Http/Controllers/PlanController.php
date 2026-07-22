<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(Request $request): View
    {
        return view('plans.index', [
            'plans' => Plan::orderBy('sort_order')->get(),
            'subscription' => $request->user()->subscription,
        ]);
    }
}
