<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $document->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-4">
                <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $document->body }}</p>

                @if ($document->attachment_path)
                    <p class="text-sm">添付: {{ basename($document->attachment_path) }}</p>
                @endif

                <a href="{{ route('documents.edit', $document) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-semibold">
                    編集する
                </a>
                <a href="{{ route('documents.index') }}" class="text-sm underline ml-2">一覧へ戻る</a>
            </div>
        </div>
    </div>
</x-app-layout>
