@extends('layouts.thermalprinter')

@section('content')
<div class="max-w-5xl mx-auto p-4 bg-white dark:bg-gray-900 dark:text-white">
    <h1 class="text-2xl font-bold mb-2">Remittance Receipt</h1>

    <div class="mb-4 text-sm text-gray-700 dark:text-gray-300">
        <div><strong>Batch ID:</strong> #{{ $batch->id }}</div>
        <div><strong>Cashier:</strong> {{ $batch->cashier->name }}</div>
        <div><strong>Agent:</strong> {{ $batch->agent->name }}</div>
        <div><strong>Date:</strong> {{ $batch->submitted_at->format('F d, Y h:i A') }}</div>
        <div><strong>Status:</strong> {{ ucfirst($batch->status) }}</div>
    </div>

    @php
        $groupedBets = $batch->receipts->map(function ($receipt) {
            return [
                'stub_id' => $receipt->stub_id,
                'total'   => $receipt->total_amount,
                'bets'    => \App\Models\Bet::where('stub_id', $receipt->stub_id)->get()
            ];
        });

        $totalOverall = $groupedBets->sum('total');
    @endphp

    @foreach ($groupedBets as $group)
        <div class="mb-8 border-b pb-4">
            <h2 class="text-lg font-semibold mb-2">Stub: {{ $group['stub_id'] }}</h2>
            <div class="space-y-2">
                @foreach ($group['bets'] as $bet)
                    <div class="flex justify-between border-b py-1">
                        <div>
                            <div><strong>{{ $bet->game_type }}</strong> - Draw: {{ $bet->game_draw }}</div>
                            <div>Number: {{ $bet->bet_number }}</div>
                        </div>
                        <div>₱{{ number_format($bet->amount, 2) }}</div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between font-bold mt-2">
                <div>Total:</div>
                <div>₱{{ number_format($group['total'], 2) }}</div>
            </div>
        </div>
    @endforeach

    <div class="flex justify-between mt-6 text-xl font-bold border-t pt-4">
        <div>Overall Total</div>
        <div>₱{{ number_format($totalOverall, 2) }}</div>
    </div>

    <div class="mt-6 flex gap-4 no-print">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Print Receipt</button>
        <a href="{{ route('cashier.receipts.batchExport', $batch->id) }}"
        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Export as PDF
        </a>

    </div>
</div>
@endsection
