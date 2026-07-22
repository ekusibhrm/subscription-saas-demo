<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('お申し込みがキャンセルされました') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 p-8 text-center">
                <div class="w-14 h-14 mx-auto rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mb-4">
                    <x-icon-x class="w-7 h-7" />
                </div>
                <p class="text-gray-700 dark:text-gray-300">お申し込み手続きはキャンセルされました。プランの変更は行われていません。</p>
                <a href="{{ route('plans.index') }}" class="mt-6 inline-flex items-center px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    プラン一覧に戻る
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
