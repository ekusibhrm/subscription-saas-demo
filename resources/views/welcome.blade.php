<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 dark:text-white">
        <x-demo-banner />

        <div class="max-w-3xl mx-auto px-6 py-16">
            <h1 class="text-3xl font-bold mb-4">{{ config('app.name') }}</h1>
            <p class="text-gray-600 dark:text-gray-300 mb-8">
                Stripeを使ったサブスクリプション課金のポートフォリオ用デモアプリです。
                Free / Pro / Enterprise のプランに応じて、ノート管理機能の利用制限が変わります。
            </p>

            <div class="flex gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-semibold">
                        ダッシュボードへ
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-semibold">
                        ログイン
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 border border-gray-400 rounded-md text-sm font-semibold">
                        新規登録
                    </a>
                @endauth
            </div>
        </div>
    </body>
</html>
