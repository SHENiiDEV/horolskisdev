<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }} - HOROLSKIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page-break {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen p-8 text-gray-900 font-sans antialiased">

    <!-- Actions Bar -->
    <div class="max-w-[210mm] mx-auto mb-6 flex justify-between items-center no-print">
        <a href="{{ route('dashboard') }}"
            class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
        </a>
        <button onclick="window.print()"
            class="inline-flex items-center px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print / Save PDF
        </button>
    </div>

    <!-- Invoice Container (A4 Proportions approx) -->
    <div
        class="max-w-[210mm] mx-auto bg-white shadow-2xl rounded-xl overflow-hidden print:shadow-none print:rounded-none">

        <!-- Top accent -->
        <div class="h-2 bg-gradient-to-r from-gray-900 to-gray-700"></div>

        <div class="p-12">
            @php \Carbon\Carbon::setLocale($invoice->language); @endphp
            <!-- Header -->
            <div class="flex justify-between items-start mb-16">
                <div>
                    <!-- Brand -->
                    <h1 class="text-4xl font-black tracking-tight text-gray-900">HOROLSKIS</h1>
                    <p class="text-sm text-gray-500 mt-1 uppercase tracking-widest font-medium">
                        {{ __('invoice.developer') }}
                    </p>
                </div>
                <div class="text-right">
                    <h2 class="text-base font-semibold text-gray-400 uppercase tracking-widest mb-2">
                        {{ __('invoice.invoice') }}
                    </h2>
                    <p class="text-4xl font-light text-gray-900">
                        #{{ $invoice->public_id ?? str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</p>
                    <div class="mt-4 flex justify-end">
                        <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" class="no-print">
                            @csrf
                            @method('PUT')
                            <select name="status" onchange="this.form.submit()"
                                class="appearance-none cursor-pointer inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border-0 focus:ring-2 focus:ring-indigo-500 {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->status === 'sent' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600') }}">
                                <option value="draft" {{ $invoice->status === 'draft' ? 'selected' : '' }}>
                                    {{ __('invoice.draft') }}
                                </option>
                                <option value="sent" {{ $invoice->status === 'sent' ? 'selected' : '' }}>
                                    {{ __('invoice.sent') }}
                                </option>
                                <option value="paid" {{ $invoice->status === 'paid' ? 'selected' : '' }}>
                                    {{ __('invoice.paid') }}
                                </option>
                            </select>
                        </form>
                        <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" class="no-print ml-2">
                            @csrf
                            @method('PUT')
                            <select name="language" onchange="this.form.submit()"
                                class="appearance-none cursor-pointer inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border-0 focus:ring-2 focus:ring-indigo-500 bg-gray-100 text-gray-600">
                                <option value="en" {{ $invoice->language === 'en' ? 'selected' : '' }}>EN</option>
                                <option value="lv" {{ $invoice->language === 'lv' ? 'selected' : '' }}>LV</option>
                            </select>
                        </form>
                        <div
                            class="hidden print:inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->status === 'sent' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600') }}">
                            {{ __('invoice.' . $invoice->status) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Addresses & Meta -->
            <div class="mb-12 border-b border-gray-100 pb-12">
                <!-- Row 1: Addresses Side by Side -->
                <div class="grid grid-cols-2 gap-12 mb-12">
                    <!-- Paid To (Left) -->
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">
                            {{ __('invoice.paid_to') }}
                        </h3>
                        <p class="font-bold text-gray-900 text-lg">Mihails Horoļskis</p>
                        <div class="text-gray-500 text-sm mt-2 leading-relaxed">
                            Lepju iela 9-45<br>
                            Riga, Latvia, LV-1016<br>
                            PK: 221194-10523<br>
                            info@horolskis.lv
                        </div>
                    </div>

                    <!-- Billed To (Right) -->
                    <div class="text-right">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">
                            {{ __('invoice.billed_to') }}
                        </h3>
                        <p class="font-bold text-gray-900 text-lg">{{ $invoice->client->name }}</p>
                        <div class="text-gray-500 text-sm mt-2 leading-relaxed">
                            @if($invoice->client->address)
                                <p>{{ $invoice->client->address }}</p>
                                <p>{{ $invoice->client->city }}, {{ $invoice->client->postal_code }}</p>
                                <p>{{ $invoice->client->country }}</p>
                            @endif
                            @if($invoice->client->vat_number)
                                <p class="mt-2">VAT: {{ $invoice->client->vat_number }}</p>
                            @endif
                            @if($invoice->client->registration_number)
                                <p>Reg: {{ $invoice->client->registration_number }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Row 2: Dates (Bottom) -->
                <div class="grid grid-cols-2 gap-12 pt-8 border-t border-gray-50">
                    <div class="text-left">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">
                            {{ __('invoice.issue_date') }}
                        </h3>
                        <p class="font-medium text-sm text-gray-900 first-letter:uppercase">
                            {{ $invoice->created_at->translatedFormat('M d, Y') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">
                            {{ __('invoice.billing_period') }}
                        </h3>
                        <p class="font-medium text-sm text-gray-900 whitespace-nowrap first-letter:uppercase">
                            {{ \Carbon\Carbon::parse($invoice->period_start)->translatedFormat('M d') }} —
                            {{ \Carbon\Carbon::parse($invoice->period_end)->translatedFormat('M d, Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="mb-12">
            <h3 class="text-xl font-bold text-gray-900 mb-6">{{ __('invoice.services_breakdown') }}</h3>

            @if($invoice->notes)
                <div class="bg-gray-50 rounded-lg p-4 mb-8 text-sm text-gray-600 leading-relaxed border border-gray-100">
                    {{ $invoice->notes }}
                </div>
            @endif

            <div class="overflow-hidden rounded-lg border border-gray-200">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50/50 text-gray-500 font-medium">
                        <tr>
                            <th class="px-6 py-4 w-[45%]">{{ __('invoice.description') }}</th>
                            <th class="px-4 py-4 text-right whitespace-nowrap">{{ __('invoice.hours_qty') }}</th>
                            <th class="px-4 py-4 text-right whitespace-nowrap">{{ __('invoice.rate') }}</th>
                            <th class="px-6 py-4 text-right whitespace-nowrap">{{ __('invoice.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($invoice->tasks as $task)
                            @php
                                $totalSeconds = $task->timeEntries->sum('duration');
                                $hours = $totalSeconds / 3600;
                                if ($task->pricing_type === 'fixed') {
                                    $cost = $task->rate ?? 0;
                                } else {
                                    $rate = $task->rate ?? 0;
                                    $cost = $rate * $hours;
                                }
                            @endphp
                            <tr class="bg-white page-break">
                                <td class="px-6 py-4 align-top">
                                    <p class="font-semibold text-gray-900">{{ $task->title }}</p>
                                    @if($task->project)
                                        <p class="text-gray-400 text-xs mb-1">
                                            {{ __('invoice.project') }}: {{ $task->project->name }}
                                            @if($task->project->domain)
                                                • {{ $task->project->domain }}
                                            @endif
                                        </p>
                                    @endif
                                    @if($task->description)
                                        <p class="text-gray-500 text-xs mt-1 leading-relaxed">
                                            {{ Str::limit($task->description, 100) }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right align-top text-gray-600 tabular-nums whitespace-nowrap">
                                    @if($task->pricing_type !== 'fixed')
                                        {{ number_format($hours, 2) }}
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right align-top text-gray-600 tabular-nums whitespace-nowrap">
                                    @if($task->pricing_type !== 'fixed')
                                        € {{ number_format($task->rate, 2) }}
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100/10 text-gray-600">{{ __('invoice.fixed') }}</span>
                                    @endif
                                </td>
                                <td
                                    class="px-6 py-4 text-right align-top font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                                    € {{ number_format($cost, 2) }}
                                </td>
                            </tr>
                        @endforeach

                        @if($invoice->items)
                            @foreach($invoice->items as $item)
                                <tr class="bg-white page-break">
                                    <td class="px-6 py-4 align-top">
                                        <p class="font-semibold text-gray-900">{{ $item['description'] }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-right align-top text-gray-600 tabular-nums whitespace-nowrap">
                                        {{ number_format((float) $item['quantity'], 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-right align-top text-gray-600 tabular-nums whitespace-nowrap">
                                        € {{ number_format((float) $item['rate'], 2) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right align-top font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                                        € {{ number_format((float) $item['amount'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3"
                                class="px-6 py-6 text-right font-medium text-gray-500 uppercase tracking-widest text-xs">
                                {{ __('invoice.total_amount') }}
                            </td>
                            <td class="px-6 py-6 text-right text-2xl font-black text-gray-900 tabular-nums">
                                €{{ number_format($invoice->amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-20 pt-8 border-t border-gray-100 flex flex-col items-center text-center">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <img src="{{ asset('img/swedbank-logo.png') }}" alt="Swedbank Logo" class="h-12 w-auto object-contain">
                <span class="text-xl font-bold text-orange-500 tracking-tight">Swedbank</span>
            </div>

            <div class="bg-gray-50 rounded-xl px-10 py-6 border border-gray-200 shadow-sm w-full max-w-lg">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">
                            {{ __('invoice.receiver') }}
                        </p>
                        <p class="text-gray-900 font-bold text-lg">MIHAILS HOROĻSKIS</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">IBAN</p>
                        <p class="font-mono text-lg text-gray-800 tracking-wide font-medium select-all">LV04 HABA
                            0551 0592 0525 3</p>
                    </div>

                    <div class="pt-2 border-t border-gray-200 mt-2">
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold text-center mb-1">
                            {{ __('invoice.payment_details') }}
                        </p>
                        <p
                            class="text-gray-900 font-medium bg-white px-3 py-1 rounded border border-gray-200 inline-block text-sm">
                            {{ __('invoice.payment_purpose') }}:
                            {{ $invoice->public_id ?? str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>
                </div>
            </div>

            <p class="mt-6 text-xs text-gray-400">{{ __('invoice.payment_due') }}</p>
        </div>

    </div>
    </div>
</body>

</html>