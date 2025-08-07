@extends('layouts.cashier')

@section('title', 'Remittance Receipt')

@section('content')
<div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">🧾 Remittance Receipt</h1>
            <p class="text-sm text-gray-500 dark:text-gray-300">Collection Date: {{ $collection->collection_date }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-300">Agent: <strong>{{ $collection->agent->name }}</strong></p>
        </div>
        <a href="#" onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow text-sm">🖨️ Print</a>
    </div>

    <div class="mb-6">
        <p class="text-sm text-gray-500 dark:text-gray-300">Total Remitted: </p>
        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">₱{{ number_format($totalAmount, 2) }}</div>
    </div>

    @foreach ($bets as $stubId => $betGroup)
        <div class="mb-6 border rounded-lg p-4 bg-white dark:bg-gray-700">
            <h2 class="font-semibold text-lg text-gray-800 dark:text-white mb-2">Stub ID: {{ $stubId }}</h2>
            <table class="w-full text-sm border-collapse">
                <thead class="bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-white">
                    <tr>
                        <th class="border px-2 py-1">#</th>
                        <th class="border px-2 py-1">Bet #</th>
                        <th class="border px-2 py-1">Amount</th>
                        <th class="border px-2 py-1">Game</th>
                        <th class="border px-2 py-1">Draw</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($betGroup as $index => $bet)
                        <tr class="text-gray-700 dark:text-gray-200">
                            <td class="border px-2 py-1">{{ $index + 1 }}</td>
                            <td class="border px-2 py-1">{{ $bet->bet_number }}</td>
                            <td class="border px-2 py-1">₱{{ number_format($bet->amount, 2) }}</td>
                            <td class="border px-2 py-1">{{ strtoupper($bet->game_type) }}</td>
                            <td class="border px-2 py-1">{{ formatDrawTime($bet->game_draw) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</div>
@endsection
