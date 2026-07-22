<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 dark:text-gray-100 leading-tight">
            {{ __('請求履歴') }}
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Stripeから取得した最新の請求情報です。</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
                @if ($invoices->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-400 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3M3.75 5.25h16.5a1.5 1.5 0 011.5 1.5v10.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6.75a1.5 1.5 0 011.5-1.5z" /></svg>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            請求履歴はまだありません。Pro/Enterpriseプランに申し込むと、ここにStripeの請求書が表示されます。
                        </p>
                    </div>
                @else
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-6 py-3 font-medium">日付</th>
                                <th class="px-6 py-3 font-medium">請求書番号</th>
                                <th class="px-6 py-3 font-medium">金額</th>
                                <th class="px-6 py-3 font-medium">ステータス</th>
                                <th class="px-6 py-3 font-medium">領収書</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($invoices as $invoice)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Carbon::createFromTimestamp($invoice->created)->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 font-mono text-gray-500 dark:text-gray-400">{{ $invoice->number ?? '-' }}</td>
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-gray-100">¥{{ number_format($invoice->amount_paid) }}</td>
                                    <td class="px-6 py-4">
                                        <span @class([
                                            'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold',
                                            'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => $invoice->status === 'paid',
                                            'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' => $invoice->status !== 'paid',
                                        ])>
                                            {{ $invoice->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($invoice->hosted_invoice_url)
                                            <a href="{{ $invoice->hosted_invoice_url }}" target="_blank" class="text-brand-600 dark:text-brand-400 hover:underline font-medium">表示</a>
                                        @endif
                                        @if ($invoice->invoice_pdf)
                                            <a href="{{ $invoice->invoice_pdf }}" target="_blank" class="text-brand-600 dark:text-brand-400 hover:underline font-medium ml-3">PDF</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
