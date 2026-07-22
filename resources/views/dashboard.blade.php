<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $subscription = auth()->user()->subscription;
        $plan = $subscription?->plan;
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-2">
                    <p>{{ __("You're logged in!") }}</p>

                    @if ($plan)
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            現在のプラン: <span class="font-semibold">{{ $plan->name }}</span>
                            @if ($subscription->onTrial())
                                (トライアル中、{{ $subscription->trial_ends_at->format('Y-m-d') }} まで)
                            @endif
                            @if ($subscription->cancel_at_period_end)
                                / {{ $subscription->current_period_end?->format('Y-m-d') }} に解約予定
                            @endif
                        </p>
                    @endif

                    <div class="flex gap-4 text-sm pt-2">
                        <a href="{{ route('documents.index') }}" class="underline">ドキュメント</a>
                        <a href="{{ route('plans.index') }}" class="underline">プラン管理</a>
                        <a href="{{ route('billing.index') }}" class="underline">請求履歴</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
