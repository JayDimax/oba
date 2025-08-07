@extends('layouts.cashier')

@section('title', 'Receipts')

@section('content')
<h2 class="text-xl font-bold mb-4">Receipts by Stub</h2>
<form method="GET" class="flex flex-wrap gap-4 mb-6 items-end" id="filterForm">
    {{-- Agent Filter --}}
    <div>
        <label for="agent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agent</label>
        <select name="agent_id" id="agent_id" class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white">
            <option value="">All Agents</option>
            @foreach ($agents as $agent)
                <option value="{{ $agent->id }}" {{ $selectedAgentId == $agent->id ? 'selected' : '' }}>
                    {{ $agent->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Single Date Filter --}}
    <div>
        <label for="filter_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Date</label>
        <input
            type="date"
            name="filter_date"
            id="filter_date"
            value="{{ request('filter_date', now()->toDateString()) }}"
            class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white"
            onchange="document.getElementById('filterForm').submit()"
        >
    </div>

    {{-- Optional Submit Button --}}
    <div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded">
            Filter
        </button>
    </div>
</form>



<div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-100 dark:bg-gray-700 dark:text-white">
            <tr>
                <th class="p-2">#</th>
                <th class="p-2">Stub ID</th>
                <th class="p-2">Game Type</th>
                <th class="p-2">Agent</th>
                <th class="p-2">Total Bets</th>
                <th class="p-2">Date</th>
                <th class="p-2">Amount</th>
                <th class="p-2 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
       @forelse ($stubs as $stub)
            @php
                $bet = $representativeBets[$stub->stub_id] ?? null;
            @endphp
            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="p-2 font-mono">{{ $loop->iteration }}</td>
                <td class="p-2 font-mono text-blue-600">{{ $stub->stub_id }}</td>
                <td class="p-2 font-mono">{{ $bet?->game_type ?? 'N/A' }}</td>
                <td class="p-2">{{ $agents->firstWhere('id', $stub->agent_id)?->name ?? 'Unknown' }}</td>
                <td class="p-2">{{ $stub->total_bets }}</td>
                <td class="p-2 text-sm text-gray-500">{{ \Carbon\Carbon::parse($stub->latest)->format('M d, Y H:i') }}</td>
                <td class="p-2 text-left">₱{{ number_format($stub->total_amount, 2) }}</td>
                <td class="p-2 text-center">
                    <a href="{{ route('cashier.receipt.printStub', $stub->stub_id) }}"
                    class="inline-block bg-indigo-600 text-white px-4 py-1 rounded hover:bg-indigo-700">Print</a>
                    <!-- <a href="{{ route('cashier.receipts.export', ['agent_id' => request('agent_id')]) }}"
                    class="inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">PDF</a> -->
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center p-4 text-gray-500">No receipts found.</td>
            </tr>
        @endforelse


        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $stubs->links() }}
</div>
@endsection
