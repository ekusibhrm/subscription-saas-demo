<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-gradient-to-b from-brand-50 via-gray-50 to-gray-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-900">
            <x-demo-banner />

            <div class="flex flex-col justify-center items-center pt-10 sm:pt-16 px-4 pb-10">
                <a href="/" class="flex items-center gap-2">
                    <x-application-logo class="w-11 h-11 text-brand-600 dark:text-brand-400" />
                    <span class="font-bold text-xl text-gray-900 dark:text-white tracking-tight">{{ config('app.name') }}</span>
                </a>

                <div class="w-full sm:max-w-md mt-8 px-6 py-8 bg-white dark:bg-gray-800 shadow-xl shadow-gray-200/60 dark:shadow-none ring-1 ring-gray-900/5 overflow-hidden sm:rounded-2xl">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
