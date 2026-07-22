<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('お申し込みありがとうございます') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 p-8 text-center">
                <div class="w-14 h-14 mx-auto rounded-full bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 flex items-center justify-center mb-4">
                    <x-icon-check class="w-7 h-7" />
                </div>
                <p class="text-gray-700 dark:text-gray-300">Stripe Checkoutでのお手続きが完了しました。</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    プランの反映はStripeからのWebhook通知を受けて数秒以内に行われます。
                    反映されない場合は<a href="{{ route('plans.index') }}" class="text-brand-600 dark:text-brand-400 hover:underline font-medium">プラン一覧</a>を再度ご確認ください。
                </p>
                <a href="{{ route('dashboard') }}" class="mt-6 inline-flex items-center px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    ダッシュボードへ戻る
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
