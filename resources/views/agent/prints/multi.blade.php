@extends('layouts.thermalprinter')

@section('content')
<style>
    body {
        font-family: monospace;
        font-size: 13px;
    }

    .center {
        text-align: center;
    }

    .bold {
        font-weight: bold;
    }

    .divider {
        border-top: 1px dashed black;
        margin: 6px 0;
    }

    .section {
        margin: 4px 0;
    }

    .small {
        font-size: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 4px 0;
    }

    th {
        font-size: 13px;
    }

    td {
        font-size: 18px;
    }
</style>

<div class="center" style="margin-bottom: 6px;">
    <img src="{{ asset('images/tlogo.png') }}" alt="Logo"
         style="width: 80px; max-width: 100%; display: inline-block;">
</div>

<div class="center bold" style="font-size: 16px;">ORCAS</div>
<div class="center bold" style="font-size: 14px;">Agent: {{ $bets->first()->betAgent->name ?? 'N/A' }}</div>

<div class="section" style="font-size: 13px;">
    <div>Draw Date: <strong>{{ $bets->first()->game_date }}</strong></div>

    @php
        $stubList = $bets->pluck('stub_id')->unique();
        $chunks = $stubList->chunk(3); // Fix: properly chunk the unique stub IDs
    @endphp

    <div>
        Txn Code(s):
        <strong>
            @foreach($chunks as $chunk)
                {{ $chunk->implode(', ') }}<br>
            @endforeach
        </strong>
    </div>
</div>

<div class="divider"></div>

<table>
    <thead>
        <tr class="bold">
            <th style="text-align: left;">Draw</th>
            <th style="text-align: left;">Game</th>
            <th style="text-align: center;">Combi</th>
            <th style="text-align: right;">Bet</th>
        </tr>
    </thead>
    <tbody>
        @php $totalAmount = 0; @endphp
        @foreach ($bets as $bet)
            <tr>
                <td style="text-align: left;">
                    {{ match ((int) $bet->game_draw) {
                        14 => '2PM',
                        17 => '5PM',
                        21 => '9PM',
                        default => $bet->game_draw,
                    } }}
                </td>
                <td style="text-align: left;">{{ strtoupper($bet->game_type) }}</td>
                <td style="text-align: center;">{{ $bet->bet_number }}</td>
                <td style="text-align: right;">{{ number_format($bet->amount, 2) }}</td>
            </tr>
            @php $totalAmount += $bet->amount; @endphp
        @endforeach
    </tbody>
</table>

<div class="divider"></div>

<div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 18px; margin-top: 6px;">
    <div>TOTAL:</div>
    <div>{{ number_format($totalAmount, 2) }}</div>
</div>

<div class="divider"></div>

{{-- ✅ QR code at bottom if single stub --}}
@if ($stubList->count() === 1)
    <div class="center section">
        {!! QrCode::size(50)->generate($stubList->first()) !!}
    </div>
@endif

<div class="center section small">
    Printed: {{ now()->format('Y-m-d H:i:s') }}
</div>

<div style="height: 60px;"></div>

<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        import { Plugins } from '@capacitor/core';
const { BluetoothPrinterPlugin } = Plugins;

// Fetch printer MAC
async function fetchPrinterMac(agentId, token) {
  try {
    const result = await BluetoothPrinterPlugin.fetchPrinterMac({ agentId, token });
    console.log('Printer MAC:', result.mac);
  } catch (e) {
    console.error('Error fetching MAC', e);
  }
}

// Select printer manually
async function selectPrinter() {
  try {
    const result = await BluetoothPrinterPlugin.selectPrinter();
    console.log('Selected MAC:', result.selectedMac);
  } catch (e) {
    console.error('Error selecting printer', e);
  }
}

// Print receipt text
async function printReceipt(text) {
  try {
    await BluetoothPrinterPlugin.printReceipt({ text });
    console.log('Print successful');
  } catch (e) {
    console.error('Print failed', e);
  }
}

    });
</script> -->

@endsection
