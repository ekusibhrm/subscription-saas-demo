<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('新規ドキュメント') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-4">
                @csrf

                <div>
                    <x-input-label for="title" value="タイトル" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="body" value="本文" />
                    <textarea id="body" name="body" rows="8" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">{{ old('body') }}</textarea>
                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                </div>

                @if (auth()->user()->currentPlan()?->allowsAttachments())
                    <div>
                        <x-input-label for="attachment" value="添付ファイル(5MBまで)" />
                        <input id="attachment" name="attachment" type="file" class="mt-1 block w-full text-sm" />
                        <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
                    </div>
                @else
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        添付ファイル機能はPro以上のプランでご利用いただけます。
                    </p>
                @endif

                <div class="flex gap-3">
                    <x-primary-button>作成する</x-primary-button>
                    <a href="{{ route('documents.index') }}" class="text-sm self-center underline">キャンセル</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
