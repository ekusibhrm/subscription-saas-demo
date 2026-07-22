<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
            {{ $document->title }}
        </h2>
        <p class="mt-1 text-xs text-gray-400">更新日: {{ $document->updated_at->format('Y年n月j日 H:i') }}</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 p-6 space-y-5">
                <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $document->body }}</p>

                @if ($document->attachment_path)
                    <p class="text-sm flex items-center gap-2 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/40 rounded-lg px-3 py-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg>
                        添付: {{ basename($document->attachment_path) }}
                    </p>
                @endif

                <div class="flex items-center gap-4 pt-2">
                    <a href="{{ route('documents.edit', $document) }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                        編集する
                    </a>
                    <a href="{{ route('documents.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium">一覧へ戻る</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
