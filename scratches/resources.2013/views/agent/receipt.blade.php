@extends('layouts.thermalprinter')

@section('content')
<style>
    .header {
        text-align: center;
        margin-bottom: 10px;
    }

    .header img {
        width: 80px;
    }

    .header-title {
        font-size: 15px;
        font-weight: bold;
    }

    .agent {
        font-size: 14px;
        font-weight: bold;
    }

    .info {
        font-size: 14px;
        margin-top: 6px;
    }

    .bets-table {
        width: 100%;
        margin-top: 10px;
        font-size: 16px; /* Bigger font for bets */
    }

    .bets-table th, .bets-table td {
        padding: 4px 0;
    }

    .total {
        margin-top: 10px;
        font-weight: bold;
        font-size: 16px;
        display: flex;
        justify-content: space-between;
    }

    .qr-section {
        text-align: center;
        font-size: 14px;
        margin-top: 10px;
    }

    .divider {
        border-top: 1px dashed black;
        margin: 8px 0;
    }
</style>

<div class="header">
    <img src="{{ asset('images/tlogo.png') }}" alt="Logo">
</div>

<div class="header-title">ORCAS</div>
<div class="agent">Agent: {{ $agentName }}</div>

<div class="info">
    Draw Date: <strong>{{ $drawDate }}</strong><br>
    Txn Code: <strong>Stub-{{ $stub }}</strong>
</div>

<div class="divider"></div>

<table class="bets-table">
    <thead>
        <tr>
            <th>Draw</th>
            <th>Game</th>
            <th style="text-align: center;">Combi</th>
            <th style="text-align: right;">Bet</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($bets as $bet)
        <tr>
            <td>
                {{ match ((int) $bet->game_draw) {
                    14 => '2PM',
                    17 => '5PM',
                    21 => '9PM',
                    default => $bet->game_draw } }}
            </td>
            <td>{{ strtoupper($bet->game_type) }}</td>
            <td style="text-align: center;">{{ $bet->bet_number }}</td>
            <td style="text-align: right;">{{ number_format($bet->amount, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="divider"></div>

<div class="total">
    <span>TOTAL:</span>
    <span>{{ number_format($totalAmount, 2) }}</span>
</div>

<div class="divider"></div>

<div class="qr-section">
    <div>Stub #: <strong>{{ $bets->first()->id ?? 1 }}</strong></div>
    <img src="data:image/png;base64,{{ $qrCodeImage }}" width="100"><br>
    <div>Stub-{{ $stub }}</div>
    <div>Printed: {{ $printedTime }}</div>
</div>

<div style="height: 50px;"></div>

    <!-- Print Button -->
    <button
        onclick="printRawBT()"
        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
    >
        Print with RawBT
    </button>
</div>

<script>
    function printRawBT() {
        const content = document.getElementById('print-content').innerText;

        const encodedText = encodeURIComponent(content);
        const rawbtURL = `intent:#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;S.text=${encodedText};end;`;

        window.location.href = rawbtURL;
    }
</script>
@endsection
