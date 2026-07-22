<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('ドキュメントを編集') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 p-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="title" value="タイトル" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full rounded-lg" :value="old('title', $document->title)" required autofocus />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="body" value="本文" />
                    <textarea id="body" name="body" rows="8" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('body', $document->body) }}</textarea>
                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                </div>

                @if (auth()->user()->currentPlan()?->allowsAttachments())
                    <div>
                        <x-input-label for="attachment" value="添付ファイル(5MBまで)" />
                        @if ($document->attachment_path)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">現在のファイル: {{ basename($document->attachment_path) }}</p>
                        @endif
                        <input id="attachment" name="attachment" type="file" class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/40 dark:file:text-brand-300" />
                        <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
                    </div>
                @else
                    <p class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/40 rounded-lg px-3 py-2">
                        添付ファイル機能はPro以上のプランでご利用いただけます。
                    </p>
                @endif

                <div class="flex items-center gap-4 pt-2">
                    <x-primary-button>更新する</x-primary-button>
                    <a href="{{ route('documents.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium">キャンセル</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
