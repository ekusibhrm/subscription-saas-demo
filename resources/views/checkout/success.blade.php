<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('お申し込みありがとうございます') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 text-sm text-gray-700 dark:text-gray-300 space-y-4">
                <p>Stripe Checkoutでのお手続きが完了しました。</p>
                <p>
                    プランの反映はStripeからのWebhook通知を受けて数秒以内に行われます。
                    反映されない場合は<a href="{{ route('plans.index') }}" class="underline">プラン一覧</a>を再度ご確認ください。
                </p>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-semibold">
                    ダッシュボードへ戻る
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
