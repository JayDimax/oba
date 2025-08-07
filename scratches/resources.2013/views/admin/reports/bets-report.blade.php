@extends('layouts.admin')

@section('title', 'Admin Bets Report')

@section('content')
<div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Bets Report</h1>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.reports.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Date</label>
            <input type="date" name="from_date" value="{{ $from }}" class="w-full border rounded px-3 py-1 dark:bg-gray-700 dark:text-white dark:border-gray-600">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Date</label>
            <input type="date" name="to_date" value="{{ $to }}" class="w-full border rounded px-3 py-1 dark:bg-gray-700 dark:text-white dark:border-gray-600">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Draw Time</label>
            <select name="draw_time" class="w-full border rounded px-3 py-1 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                <option value="ALL" {{ $draw == 'ALL' ? 'selected' : '' }}>ALL</option>
                <option value="2PM" {{ $draw == '2PM' ? 'selected' : '' }}>2PM</option>
                <option value="5PM" {{ $draw == '5PM' ? 'selected' : '' }}>5PM</option>
                <option value="9PM" {{ $draw == '9PM' ? 'selected' : '' }}>9PM</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agent Code</label>
            <input type="text" name="agent_code" value="{{ $agentCode }}" placeholder="e.g. AGT001" class="w-full border rounded px-3 py-1 dark:bg-gray-700 dark:text-white dark:border-gray-600">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agent Name</label>
            <input type="text" name="agent_name" value="{{ $agentName }}" placeholder="e.g. Juan Dela Cruz" class="w-full border rounded px-3 py-1 dark:bg-gray-700 dark:text-white dark:border-gray-600">
        </div>

        <div class="col-span-1 md:col-span-5 flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                🔍 Filter
            </button>
        </div>
    </form>

    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded shadow text-center">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Net Sales</h2>
            <p class="text-2xl text-blue-600 font-semibold mt-2">₱{{ number_format($netSales, 2) }}</p>
        </div>
        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded shadow text-center">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Total Commission</h2>
            <p class="text-2xl text-green-600 font-semibold mt-2">₱{{ number_format($totalCommission, 2) }}</p>
        </div>
    </div>

    {{-- Print Button --}}
    <div class="mb-4 flex justify-end">
        <button
            onclick="window.print()"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition"
            title="Print the current table">
            🖨️ Print Table
        </button>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border border-gray-300 dark:border-gray-700 text-sm">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold">
                <tr>
                    <th class="px-4 py-2">Agent Code</th>
                    <th class="px-4 py-2">Game Time</th>
                    <th class="px-4 py-2">Game Type</th>
                    <th class="px-4 py-2">Bet #</th>
                    <th class="px-4 py-2 text-right">Amount</th>
                    <th class="px-4 py-2">Customer</th>
                    <th class="px-4 py-2 text-right">Commission</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 dark:text-gray-100">
                @forelse ($bets as $bet)
                    <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="px-4 py-2">{{ $bet->agent->code ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $bet->game_draw }}</td>
                        <td class="px-4 py-2">{{ $bet->game_type }}</td>
                        <td class="px-4 py-2">{{ $bet->id }}</td>
                        <td class="px-4 py-2 text-right">₱{{ number_format($bet->amount, 2) }}</td>
                        <td class="px-4 py-2">{{ $bet->customer_name ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">₱{{ number_format($bet->commission + $bet->commission_bonus, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500 dark:text-gray-400">
                            No bets found for this filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Print Styles --}}
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .p-4.bg-white.rounded-lg.shadow,
    .p-4.bg-white.rounded-lg.shadow * {
        visibility: visible;
    }
    .p-4.bg-white.rounded-lg.shadow {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    form, button, .mb-4.flex {
        display: none !important;
    }
}
</style>
@endsection
