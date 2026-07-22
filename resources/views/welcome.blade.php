<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Stripeによるサブスクリプション課金のデモ。Free / Pro / Enterprise のプランに応じてノート管理機能の利用範囲が変わります。">
        <title>{{ config('app.name') }} — シンプルなノート管理のサブスクリプションSaaS</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">
        <x-demo-banner />

        <!-- Simple marketing nav -->
        <header class="sticky top-0 z-40 bg-white/90 dark:bg-gray-900/90 backdrop-blur border-b border-gray-100 dark:border-gray-800">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2">
                    <x-application-logo class="w-8 h-8 text-brand-600 dark:text-brand-400" />
                    <span class="font-bold text-gray-900 dark:text-white tracking-tight">{{ config('app.name') }}</span>
                </a>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                            ダッシュボードへ
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white text-sm font-semibold transition">
                            ログイン
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                            無料で始める
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 to-white dark:from-gray-800/40 dark:to-gray-900"></div>
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20 text-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-100 dark:bg-brand-900/50 text-brand-700 dark:text-brand-300 text-xs font-semibold mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                    Stripeテストモードのサブスクリプション課金デモ
                </span>

                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                    ノートを、<span class="text-brand-600 dark:text-brand-400">プランに合わせて</span>整理する。
                </h1>

                <p class="mt-5 text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                    Free・Pro・Enterpriseのプランごとに使える機能が変わる、シンプルなノート管理アプリです。
                    14日間の無料トライアル付きで、いつでもプラン変更・解約ができます。
                </p>

                <div class="mt-8 flex items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-lg font-semibold shadow-sm transition">
                        無料で始める
                    </a>
                    <a href="#pricing" class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg font-semibold transition">
                        料金プランを見る
                    </a>
                </div>

                <p class="mt-4 text-xs text-gray-400">
                    実際の課金は発生しません。テストカード <code class="font-mono">4242 4242 4242 4242</code> でお試しいただけます。
                </p>
            </div>
        </section>

        <!-- Features -->
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                <div>
                    <div class="w-11 h-11 rounded-xl bg-brand-100 dark:bg-brand-900/50 text-brand-600 dark:text-brand-400 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">14日間の無料トライアル</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Pro・Enterpriseプランはクレジットカード登録後も14日間は無料。気に入らなければトライアル中にいつでもキャンセルできます。
                    </p>
                </div>
                <div>
                    <div class="w-11 h-11 rounded-xl bg-brand-100 dark:bg-brand-900/50 text-brand-600 dark:text-brand-400 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3M3.75 5.25h16.5a1.5 1.5 0 011.5 1.5v10.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6.75a1.5 1.5 0 011.5-1.5z" /></svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">安心・安全な決済</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        決済処理はすべてStripeが担当。カード情報が当サイトのサーバーを通ることはありません。
                    </p>
                </div>
                <div>
                    <div class="w-11 h-11 rounded-xl bg-brand-100 dark:bg-brand-900/50 text-brand-600 dark:text-brand-400 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75M3.75 3h9.879a1.5 1.5 0 011.06.44l3.622 3.62a1.5 1.5 0 01.44 1.061V19.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V4.5a1.5 1.5 0 011.5-1.5z" /></svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">プランに合わせて広がる機能</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Freeはノート3件まで。Pro以上で件数無制限・添付ファイルにも対応します。
                    </p>
                </div>
            </div>
        </section>

        <!-- Pricing teaser -->
        <section id="pricing" class="bg-gray-50 dark:bg-gray-800/40 py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">シンプルな料金プラン</h2>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">いつでもアップグレード・ダウングレード・解約できます。</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    @foreach ($plans as $plan)
                        <div @class([
                            'rounded-2xl p-6 bg-white dark:bg-gray-800 ring-1 relative flex flex-col h-full',
                            'ring-brand-600 shadow-xl shadow-brand-100 dark:shadow-none scale-[1.02]' => $plan->slug === 'pro',
                            'ring-gray-200 dark:ring-gray-700 shadow-sm' => $plan->slug !== 'pro',
                        ])>
                            @if ($plan->slug === 'pro')
                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-brand-600 text-white text-xs font-semibold shadow-sm">
                                    人気No.1
                                </span>
                            @endif

                            <h3 class="font-semibold text-lg text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                            <p class="mt-2">
                                <span class="text-3xl font-extrabold text-gray-900 dark:text-white">¥{{ number_format($plan->price_jpy) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">/月</span>
                            </p>
                            @if ($plan->trial_days > 0)
                                <p class="mt-1 text-xs font-medium text-brand-600 dark:text-brand-400">{{ $plan->trial_days }}日間無料トライアル</p>
                            @endif

                            <ul class="mt-6 space-y-3 text-sm flex-1">
                                <li class="flex items-start gap-2">
                                    <x-icon-check class="w-5 h-5 text-brand-600 dark:text-brand-400 shrink-0" />
                                    <span class="text-gray-600 dark:text-gray-300">
                                        ドキュメント{{ $plan->documentLimit() === null ? '無制限' : $plan->documentLimit().'件まで' }}
                                    </span>
                                </li>
                                <li class="flex items-start gap-2">
                                    @if ($plan->allowsAttachments())
                                        <x-icon-check class="w-5 h-5 shrink-0 text-brand-600 dark:text-brand-400" />
                                    @else
                                        <x-icon-x class="w-5 h-5 shrink-0 text-gray-300 dark:text-gray-600" />
                                    @endif
                                    <span @class(['text-gray-600 dark:text-gray-300' => $plan->allowsAttachments(), 'text-gray-400 dark:text-gray-600' => ! $plan->allowsAttachments()])>
                                        添付ファイル
                                    </span>
                                </li>
                                <li class="flex items-start gap-2">
                                    @if ($plan->hasPrioritySupport())
                                        <x-icon-check class="w-5 h-5 shrink-0 text-brand-600 dark:text-brand-400" />
                                    @else
                                        <x-icon-x class="w-5 h-5 shrink-0 text-gray-300 dark:text-gray-600" />
                                    @endif
                                    <span @class(['text-gray-600 dark:text-gray-300' => $plan->hasPrioritySupport(), 'text-gray-400 dark:text-gray-600' => ! $plan->hasPrioritySupport()])>
                                        優先サポート
                                    </span>
                                </li>
                            </ul>

                            <a href="{{ route('register') }}"
                               @class([
                                   'mt-6 inline-flex justify-center px-4 py-2.5 rounded-lg text-sm font-semibold transition',
                                   'bg-brand-600 hover:bg-brand-700 text-white shadow-sm' => $plan->slug === 'pro',
                                   'border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-900 dark:text-white' => $plan->slug !== 'pro',
                               ])>
                                {{ $plan->isFree() ? '無料で始める' : '登録してこのプランを試す' }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-10 border-t border-gray-100 dark:border-gray-800">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <x-application-logo class="w-6 h-6 text-brand-600 dark:text-brand-400" />
                    <span class="font-semibold text-sm text-gray-700 dark:text-gray-300">{{ config('app.name') }}</span>
                </div>
                <p class="text-xs text-gray-400 text-center">
                    これはポートフォリオ用のデモアプリです。実際の課金・サービス提供は行っておりません。
                </p>
            </div>
        </footer>
    </body>
</html>
