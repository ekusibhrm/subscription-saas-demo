<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('ダッシュボード') }}
        </h2>
    </x-slot>

    @php
        $subscription = auth()->user()->subscription;
        $plan = $subscription?->plan;
        $documentCount = auth()->user()->documents()->count();
        $documentLimit = $plan?->documentLimit();
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    おかえりなさい、{{ auth()->user()->name }} さん 👋
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">現在の契約状況とドキュメントの利用状況です。</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">現在のプラン</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $plan?->name ?? '-' }}</p>
                    @if ($subscription?->onTrial())
                        <p class="mt-1 text-xs text-brand-600 dark:text-brand-400">
                            トライアル中・{{ $subscription->trial_ends_at->format('Y年n月j日') }} まで
                        </p>
                    @elseif ($subscription?->cancel_at_period_end)
                        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                            {{ $subscription->current_period_end?->format('Y年n月j日') }} に解約予定
                        </p>
                    @else
                        <p class="mt-1 text-xs text-gray-400">ステータス: {{ $subscription?->status ?? '-' }}</p>
                    @endif
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">ドキュメント</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $documentCount }}<span class="text-base font-medium text-gray-400">{{ $documentLimit !== null ? ' / '.$documentLimit.'件' : ' 件(無制限)' }}</span>
                    </p>
                    @if ($documentLimit !== null)
                        <div class="mt-2 h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                            <div class="h-full bg-brand-500" style="width: {{ min(100, (int) ($documentCount / max($documentLimit, 1) * 100)) }}%"></div>
                        </div>
                    @endif
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">添付ファイル / 優先サポート</p>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 space-y-1">
                        <span class="flex items-center gap-1.5">
                            @if ($plan?->allowsAttachments())
                                <x-icon-check class="w-4 h-4 text-brand-600 dark:text-brand-400" /> 添付ファイル利用可
                            @else
                                <x-icon-x class="w-4 h-4 text-gray-300 dark:text-gray-600" /> 添付ファイル利用不可
                            @endif
                        </span>
                        <span class="flex items-center gap-1.5">
                            @if ($plan?->hasPrioritySupport())
                                <x-icon-check class="w-4 h-4 text-brand-600 dark:text-brand-400" /> 優先サポートあり
                            @else
                                <x-icon-x class="w-4 h-4 text-gray-300 dark:text-gray-600" /> 優先サポートなし
                            @endif
                        </span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <a href="{{ route('documents.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 p-6 hover:ring-brand-300 dark:hover:ring-brand-700 hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-lg bg-brand-100 dark:bg-brand-900/50 text-brand-600 dark:text-brand-400 flex items-center justify-center mb-3 group-hover:scale-105 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75M3.75 3h9.879a1.5 1.5 0 011.06.44l3.622 3.62a1.5 1.5 0 01.44 1.061V19.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V4.5a1.5 1.5 0 011.5-1.5z" /></svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white">ドキュメント</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">ノートの作成・管理</p>
                </a>
                <a href="{{ route('plans.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 p-6 hover:ring-brand-300 dark:hover:ring-brand-700 hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-lg bg-brand-100 dark:bg-brand-900/50 text-brand-600 dark:text-brand-400 flex items-center justify-center mb-3 group-hover:scale-105 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white">プラン管理</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">アップグレード・解約</p>
                </a>
                <a href="{{ route('billing.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 p-6 hover:ring-brand-300 dark:hover:ring-brand-700 hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-lg bg-brand-100 dark:bg-brand-900/50 text-brand-600 dark:text-brand-400 flex items-center justify-center mb-3 group-hover:scale-105 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3M3.75 5.25h16.5a1.5 1.5 0 011.5 1.5v10.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6.75a1.5 1.5 0 011.5-1.5z" /></svg>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white">請求履歴</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">領収書・支払い状況</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
