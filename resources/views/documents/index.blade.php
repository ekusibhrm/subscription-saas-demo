<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('ドキュメント') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 px-4 py-3 rounded-md text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ $documents->count() }}件
                    @if ($plan?->documentLimit() !== null)
                        / 上限{{ $plan->documentLimit() }}件({{ $plan->name }}プラン)
                    @endif
                </p>

                @can('create', \App\Models\Document::class)
                    <a href="{{ route('documents.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-semibold">
                        新規作成
                    </a>
                @else
                    <a href="{{ route('plans.index') }}" class="text-sm text-amber-700 dark:text-amber-400 underline">
                        上限に達しています。プランをアップグレード
                    </a>
                @endcan
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($documents as $document)
                    <div class="p-6 flex items-center justify-between">
                        <div>
                            <a href="{{ route('documents.show', $document) }}" class="font-semibold hover:underline">{{ $document->title }}</a>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $document->updated_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="flex gap-3 text-sm">
                            <a href="{{ route('documents.edit', $document) }}" class="underline">編集</a>
                            <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('削除しますか?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 dark:text-red-400 underline">削除</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="p-6 text-sm text-gray-500 dark:text-gray-400">ドキュメントはまだありません。</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
