<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('ドキュメント') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="flex items-start gap-2 bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-4 py-3 rounded-xl text-sm ring-1 ring-green-200 dark:ring-green-800">
                    <x-icon-check class="w-5 h-5 shrink-0 mt-0.5" />
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $documents->count() }}件
                        @if ($plan?->documentLimit() !== null)
                            <span class="text-gray-400">/ 上限{{ $plan->documentLimit() }}件({{ $plan->name }}プラン)</span>
                        @endif
                    </p>
                </div>

                @can('create', \App\Models\Document::class)
                    <a href="{{ route('documents.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" /></svg>
                        新規作成
                    </a>
                @else
                    <a href="{{ route('plans.index') }}" class="text-sm text-amber-700 dark:text-amber-400 font-medium hover:underline">
                        上限に達しています。プランをアップグレード →
                    </a>
                @endcan
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden">
                @forelse ($documents as $document)
                    <div class="p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-lg bg-brand-100 dark:bg-brand-900/50 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-4.5 h-4.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75M3.75 3h9.879a1.5 1.5 0 011.06.44l3.622 3.62a1.5 1.5 0 01.44 1.061V19.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V4.5a1.5 1.5 0 011.5-1.5z" /></svg>
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('documents.show', $document) }}" class="font-semibold text-gray-900 dark:text-gray-100 hover:text-brand-600 dark:hover:text-brand-400 truncate block">{{ $document->title }}</a>
                                <p class="text-xs text-gray-400">{{ $document->updated_at->format('Y-m-d H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-sm shrink-0 ml-4">
                            <a href="{{ route('documents.edit', $document) }}" class="text-gray-500 hover:text-brand-600 dark:hover:text-brand-400 font-medium">編集</a>
                            <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('削除しますか?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-600 font-medium">削除</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75M3.75 3h9.879a1.5 1.5 0 011.06.44l3.622 3.62a1.5 1.5 0 01.44 1.061V19.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V4.5a1.5 1.5 0 011.5-1.5z" /></svg>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">ドキュメントはまだありません。</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
