@extends('layouts.cashier')

@section('title', 'Dashboard')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-4 gap-6">

    <div class="bg-white dark:bg-gray-900 dark:border dark:border-gray-700 shadow rounded p-4">
        <h3 class="text-sm text-gray-500 dark:text-gray-400">Total Bets Today</h3>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">₱{{ number_format($totalBetsToday, 2) }}</p>
    </div>

    <div class="bg-white dark:bg-gray-900 dark:border dark:border-gray-700 shadow rounded p-4">
        <h3 class="text-sm text-gray-500 dark:text-gray-400">Total Collected</h3>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">₱{{ number_format($totalCollected, 2) }}</p>
    </div>

        {{-- System Deficit Alert --}}
        @if($systemDeficit > 0)
            <div class="bg-red-100 dark:bg-red-900 dark:border dark:border-red-700 shadow rounded p-4">
                <strong class="text-red-700 dark:text-red-300">System Deficit Alert:</strong>
                <p class="text-red-700 dark:text-red-300 mt-2">
                    The system has a deficit of <span class="font-semibold">₱{{ number_format($systemDeficit, 2) }}</span> today.<br>
                    Total winnings + incentives exceed total bets placed.
                </p>
            </div>
        @endif

    <div class="bg-white dark:bg-gray-900 dark:border dark:border-gray-700 shadow rounded p-4">
        <h3 class="text-sm text-gray-500 dark:text-gray-400">Pending Agents</h3>
        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $pendingAgentsCount }}</p>
    </div>
    
</div>

<div class="mt-8">
    <h4 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Agents with Pending Remittance</h4>
    <ul class="bg-white dark:bg-gray-900 dark:border dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700 rounded shadow">
        @forelse($pendingAgents as $agent)
            <li class="p-4 flex justify-between items-center text-gray-800 dark:text-gray-200">
                <span class="font-medium">{{ $agent->name }}</span>
                <span class="text-red-500 dark:text-red-400 font-semibold">₱{{ number_format($agent->unpaid_amount, 2) }}</span>
            </li>
        @empty
            <li class="p-4 text-center text-gray-500 dark:text-gray-400">All remittances are settled.</li>
        @endforelse
    </ul>
</div>

<!-- filter -->
<div class="max-w-7xl mx-auto px-4 py-6">
    <form method="GET" action="{{ route('cashier.dashboard') }}" class="mb-6 flex items-end gap-4 flex-wrap">
        <div>
            <label for="game_date" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Select Game Date</label>
            <input type="date" name="game_date" id="game_date"
                value="{{ $gameDate }}"
                class="border rounded px-3 py-2 text-sm shadow focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
        </div>
        <div>
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Filter
            </button>
        </div>
    </form>

    <!-- table -->
    <h1 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">🧾 Agent Remittance Summary</h1>

    @forelse ($agents as $agent)
        <div class="bg-white dark:bg-gray-900 dark:border dark:border-gray-700 shadow rounded-lg p-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">{{ $agent->name }}</h2>

            @if ($agent->collections->isEmpty())
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">No remittances found.</p>
            @else
                <table class="w-full mt-4 text-sm text-left text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded">
                    <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">Date</th>
                            <th class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">Gross Sales</th>
                            <th class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">Amount Due</th>
                            <th class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">Total Remitted</th>
                            <th class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">Status</th>
                            <th class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">Verified By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @foreach ($agent->collections as $collection)
                            <tr>
                                <td class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">{{ \Carbon\Carbon::parse($collection->collection_date)->toFormattedDateString() }}</td>
                                <td class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">₱{{ number_format($collection->gross, 2) }}</td>
                                <td class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">₱{{ number_format($totalNetCollected, 2) }}</td>
                                <td class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">₱{{ number_format($agent->total_incoming_remittance, 2) }}</td>
                                <td class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">
                                    <span class="px-2 py-1 rounded
                                        @if($collection->status === 'approved') bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-200
                                        @elseif($collection->status === 'pending') bg-yellow-200 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200
                                        @else bg-red-200 text-red-800 dark:bg-red-800 dark:text-red-200 @endif">
                                        {{ ucfirst($collection->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">
                                    {{ optional($collection->verifiedBy)->name ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @empty
        <p class="text-gray-600 dark:text-gray-300">No agents found.</p>
    @endforelse
</div>

@endsection
