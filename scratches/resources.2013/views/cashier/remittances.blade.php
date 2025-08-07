@extends('layouts.cashier')

@section('title', 'Remittance')

@section('content')
<h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100">Receipts Remittances</h2>

<!-- Filter for Agent Remittance -->
<div class="mb-4 flex flex-wrap gap-4">
    <form method="GET" action="{{ route('cashier.remittance') }}" class="flex gap-2 items-end text-sm">
        <div>
            <label for="agent_id" class="block font-medium text-gray-700 dark:text-gray-300">Agent</label>
            <select name="agent_id" id="agent_id" class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
                <option value="">All</option>
                @foreach ($assignedAgents as $agent)
                    <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                        {{ $agent->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="start_date" class="block font-medium text-gray-700 dark:text-gray-300">From</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
        </div>
        <div>
            <label for="end_date" class="block font-medium text-gray-700 dark:text-gray-300">To</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-1.5 rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
            Filter
        </button>
    </form>
</div>
{{--  
<form method="POST" action="{{ route('cashier.bets.markPaid') }}" x-data="remitPOS({{ $bets->sum('amount') }})">
    @csrf

<!-- Table: Pending Receipts -->
    <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded shadow border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                <tr>
                    <th class="p-2 text-left">Stub ID</th>
                    <th class="p-2 text-left">Agent</th>
                    <th class="p-2 text-left">Date</th>
                    <th class="p-2 text-right">Amount (₱)</th>
                    <th class="p-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($queuedReceipts as $receipt)
                    <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-700">
                        <td class="p-2 font-mono text-xs text-blue-700 dark:text-blue-300">
                            {{ $receipt->stub_id }}
                        </td>
                        <td class="p-2 text-sm text-gray-800 dark:text-white">
                            {{ $receipt->agent->name ?? 'Unknown' }}
                        </td>
                        <td class="p-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ $receipt->remitted_at?->format('M d, Y H:i') ?? '—' }}
                        </td>
                        <td class="p-2 text-right text-gray-900 dark:text-white">
                            ₱{{ number_format($receipt->total_amount, 2) }}
                        </td>
                        <td class="p-2 text-center uppercase font-semibold text-yellow-600 dark:text-yellow-400">
                            {{ ucfirst($receipt->status) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center p-4 text-gray-500 dark:text-gray-400">
                            No queued remittances.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div> --}}

    {{-- <!-- POS Modal -->
    <div x-show="posOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded shadow-md w-96">
            <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">Remittance Payment</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Due</label>
                <div class="text-lg font-bold">₱<span x-text="totalDue.toFixed(2)"></span></div>
            </div>

            <div class="mb-4">
                <label for="cash_received" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cash Tendered</label>
                <input type="number" step="0.01" min="0" x-model="cashReceived" @input="updateChange"
                       name="cash_tendered"
                       class="w-full mt-1 px-3 py-2 border rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Change</label>
                <div class="text-lg font-bold text-green-600 dark:text-green-400">
                    ₱<span x-text="change >= 0 ? change.toFixed(2) : '0.00'"></span>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remaining Balance</label>
                <div class="text-lg font-bold text-red-600 dark:text-red-400">
                    ₱<span x-text="balance > 0 ? balance.toFixed(2) : '0.00'"></span>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" @click="posOpen = false" class="px-3 py-1 bg-gray-300 dark:bg-gray-700 rounded hover:bg-gray-400 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" @click="finalizeSubmission"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                    Confirm Payment
                </button>
            </div>
        </div>
    </div>
</form> --}}
    <!-- Success Notification -->
    <div x-show="showSuccess"
        x-transition
        class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded text-sm font-medium">
        ✅ Payment successfully saved!
    </div>



@include('partials.pos')
<!-- Remittance Batches Table with matching style -->
    @foreach ($collections as $collection)
    <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="flex justify-between items-center mb-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $collection->agent->name }}</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($collection->collection_date)->format('F j, Y') }}</p>
        </div>

        <div class="space-y-4">
        @foreach ($collection->collectionStubs as $stub)
            @php
            $totalAmount = $stub->bets->sum('amount');
            $totalBets = $stub->bets->count();
            $latest = $stub->bets->max('created_at');
            @endphp

            <div class="p-4 bg-white dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col sm:flex-row sm:justify-between sm:items-center">
            <div class="mb-2 sm:mb-0">
                <p class="font-medium text-gray-800 dark:text-gray-200">Stub ID: <span class="font-normal">{{ $stub->stub_id }}</span></p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Latest Bet: {{ \Carbon\Carbon::parse($latest)->format('M d, Y h:i A') }}</p>
            </div>
            <div class="text-right sm:text-left">
                {{-- <p class="text-gray-700 dark:text-gray-300">Total Bets: <span class="font-semibold">{{ $totalBets }}</span></p> --}}
                <p class="text-gray-700 dark:text-gray-300">Amount: <span class="font-semibold">₱{{ number_format($totalAmount, 2) }}</span></p>
            </div>
            </div>
        @endforeach
        </div>
    </div>
    @endforeach
    <div class="mt-4">
    {{ $collections->links() }}
</div>
  

@endsection

