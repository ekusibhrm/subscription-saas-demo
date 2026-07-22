<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('請求履歴') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                @if ($invoices->isEmpty())
                    <p class="p-6 text-sm text-gray-500 dark:text-gray-400">
                        請求履歴はまだありません。Pro/Enterpriseプランに申し込むと、ここにStripeの請求書が表示されます。
                    </p>
                @else
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-3">日付</th>
                                <th class="px-6 py-3">請求書番号</th>
                                <th class="px-6 py-3">金額</th>
                                <th class="px-6 py-3">ステータス</th>
                                <th class="px-6 py-3">領収書</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4">{{ \Illuminate\Support\Carbon::createFromTimestamp($invoice->created)->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 font-mono">{{ $invoice->number ?? '-' }}</td>
                                    <td class="px-6 py-4">¥{{ number_format($invoice->amount_paid) }}</td>
                                    <td class="px-6 py-4">{{ $invoice->status }}</td>
                                    <td class="px-6 py-4">
                                        @if ($invoice->hosted_invoice_url)
                                            <a href="{{ $invoice->hosted_invoice_url }}" target="_blank" class="underline">表示</a>
                                        @endif
                                        @if ($invoice->invoice_pdf)
                                            <a href="{{ $invoice->invoice_pdf }}" target="_blank" class="underline ml-2">PDF</a>
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
