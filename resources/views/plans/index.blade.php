<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('プラン') }}
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            いつでもアップグレード・ダウングレード・解約ができます。
        </p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="flex items-start gap-2 bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-4 py-3 rounded-xl text-sm ring-1 ring-green-200 dark:ring-green-800">
                    <x-icon-check class="w-5 h-5 shrink-0 mt-0.5" />
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($subscription)
                <div class="bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 p-6 flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-11 h-11 rounded-xl bg-brand-100 dark:bg-brand-900/50 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold">
                            {{ mb_substr($subscription->plan->name, 0, 1) }}
                        </div>
                        <div class="text-sm">
                            <p class="text-gray-500 dark:text-gray-400">現在のプラン</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $subscription->plan->name }}
                                <span class="ml-1 text-xs font-normal text-gray-400">({{ $subscription->status }})</span>
                            </p>
                            @if ($subscription->onTrial())
                                <p class="text-xs text-brand-600 dark:text-brand-400 mt-0.5">トライアル中・{{ $subscription->trial_ends_at->format('Y年n月j日') }} まで無料</p>
                            @endif
                        </div>
                    </div>

                    @if ($subscription->cancel_at_period_end)
                        <div class="text-sm text-amber-700 dark:text-amber-400 flex items-center gap-3">
                            <span>{{ $subscription->current_period_end?->format('Y年n月j日') }} にFreeプランへ移行予定</span>
                            <form method="POST" action="{{ route('subscription.resume') }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-amber-300 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/30 font-semibold transition">
                                    解約を取り消す
                                </button>
                            </form>
                        </div>
                    @elseif ($subscription->stripe_subscription_id)
                        <form method="POST" action="{{ route('subscription.cancel') }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 font-semibold">
                                解約する
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                @foreach ($plans as $plan)
                    @php
                        $isCurrent = $subscription && $subscription->plan->id === $plan->id && $subscription->isActive() && ! $subscription->cancel_at_period_end;
                    @endphp
                    <div @class([
                        'rounded-2xl p-6 bg-white dark:bg-gray-800 ring-1 relative flex flex-col h-full',
                        'ring-brand-600 shadow-xl shadow-brand-100 dark:shadow-none' => $plan->slug === 'pro',
                        'ring-gray-200 dark:ring-gray-700 shadow-sm' => $plan->slug !== 'pro',
                    ])>
                        @if ($plan->slug === 'pro')
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-brand-600 text-white text-xs font-semibold shadow-sm">
                                人気No.1
                            </span>
                        @endif

                        <h3 class="font-semibold text-lg text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                        <p class="mt-2">
                            <span class="text-3xl font-extrabold text-gray-900 dark:text-white">¥{{ number_format($plan->price_jpy) }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">/月</span>
                        </p>
                        @if ($plan->trial_days > 0)
                            <p class="mt-1 text-xs font-medium text-brand-600 dark:text-brand-400">{{ $plan->trial_days }}日間無料トライアル</p>
                        @endif

                        <ul class="mt-6 space-y-3 text-sm flex-1">
                            <li class="flex items-start gap-2">
                                <x-icon-check class="w-5 h-5 shrink-0 text-brand-600 dark:text-brand-400" />
                                <span class="text-gray-600 dark:text-gray-300">
                                    ドキュメント{{ $plan->documentLimit() === null ? '無制限' : $plan->documentLimit().'件まで' }}
                                </span>
                            </li>
                            <li class="flex items-start gap-2">
                                @if ($plan->allowsAttachments())
                                    <x-icon-check class="w-5 h-5 shrink-0 text-brand-600 dark:text-brand-400" />
                                @else
                                    <x-icon-x class="w-5 h-5 shrink-0 text-gray-300 dark:text-gray-600" />
                                @endif
                                <span @class(['text-gray-600 dark:text-gray-300' => $plan->allowsAttachments(), 'text-gray-400 dark:text-gray-600' => ! $plan->allowsAttachments()])>
                                    添付ファイル
                                </span>
                            </li>
                            <li class="flex items-start gap-2">
                                @if ($plan->hasPrioritySupport())
                                    <x-icon-check class="w-5 h-5 shrink-0 text-brand-600 dark:text-brand-400" />
                                @else
                                    <x-icon-x class="w-5 h-5 shrink-0 text-gray-300 dark:text-gray-600" />
                                @endif
                                <span @class(['text-gray-600 dark:text-gray-300' => $plan->hasPrioritySupport(), 'text-gray-400 dark:text-gray-600' => ! $plan->hasPrioritySupport()])>
                                    優先サポート
                                </span>
                            </li>
                        </ul>

                        @if ($isCurrent)
                            <span class="mt-6 text-center text-sm font-semibold text-brand-700 dark:text-brand-400 border border-brand-200 dark:border-brand-700 bg-brand-50 dark:bg-brand-900/30 rounded-lg py-2.5">
                                現在のプラン
                            </span>
                        @else
                            <form method="POST" action="{{ route('subscription.subscribe', $plan) }}">
                                @csrf
                                <button type="submit" @class([
                                    'w-full inline-flex justify-center px-4 py-2.5 rounded-lg text-sm font-semibold transition mt-6',
                                    'bg-brand-600 hover:bg-brand-700 text-white shadow-sm' => $plan->slug === 'pro',
                                    'border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-900 dark:text-white' => $plan->slug !== 'pro',
                                ])>
                                    {{ $plan->isFree() ? 'Freeにダウングレード' : 'このプランにする' }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
