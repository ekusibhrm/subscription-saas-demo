<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('プラン') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 px-4 py-3 rounded-md text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($subscription)
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 text-sm text-gray-700 dark:text-gray-300">
                    現在のプラン: <span class="font-semibold">{{ $subscription->plan->name }}</span>
                    /ステータス: <span class="font-mono">{{ $subscription->status }}</span>
                    @if ($subscription->onTrial())
                        (トライアル中、{{ $subscription->trial_ends_at->format('Y-m-d') }} まで)
                    @endif
                    @if ($subscription->cancel_at_period_end)
                        <div class="mt-2 text-amber-700 dark:text-amber-400">
                            {{ $subscription->current_period_end?->format('Y-m-d') }} をもって解約(Freeプランへ移行)予定です。
                            <form method="POST" action="{{ route('subscription.resume') }}" class="inline">
                                @csrf
                                <button type="submit" class="underline">解約を取り消す</button>
                            </form>
                        </div>
                    @elseif ($subscription->stripe_subscription_id)
                        <div class="mt-2">
                            <form method="POST" action="{{ route('subscription.cancel') }}">
                                @csrf
                                <button type="submit" class="text-red-600 dark:text-red-400 underline">解約する</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($plans as $plan)
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 flex flex-col">
                        <h3 class="text-lg font-bold">{{ $plan->name }}</h3>
                        <p class="text-2xl font-bold my-2">
                            @if ($plan->price_jpy > 0)
                                ¥{{ number_format($plan->price_jpy) }}<span class="text-sm font-normal">/月</span>
                            @else
                                ¥0
                            @endif
                        </p>

                        <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-1 flex-1 mb-4">
                            <li>
                                ドキュメント:
                                {{ $plan->documentLimit() === null ? '無制限' : $plan->documentLimit().'件まで' }}
                            </li>
                            <li>添付ファイル: {{ $plan->allowsAttachments() ? '利用可' : '利用不可' }}</li>
                            <li>優先サポート: {{ $plan->hasPrioritySupport() ? 'あり' : 'なし' }}</li>
                            @if ($plan->trial_days > 0)
                                <li>{{ $plan->trial_days }}日間の無料トライアルあり</li>
                            @endif
                        </ul>

                        @if ($subscription && $subscription->plan->id === $plan->id && $subscription->isActive() && ! $subscription->cancel_at_period_end)
                            <span class="text-center text-sm font-semibold text-green-700 dark:text-green-400 border border-green-300 dark:border-green-700 rounded-md py-2">
                                現在のプラン
                            </span>
                        @else
                            <form method="POST" action="{{ route('subscription.subscribe', $plan) }}">
                                @csrf
                                <button type="submit" class="w-full inline-flex justify-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-semibold">
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
