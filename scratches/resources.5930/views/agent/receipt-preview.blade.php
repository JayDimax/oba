@extends('layouts.thermalprinter')

@section('content')
<style>
    body {
        font-family: monospace;
        font-size: 14px;
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

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    th, td {
        padding: 3px 0;
    }

    th {
        font-size: 14px;
    }

    td {
        font-size: 14px;
    }

    .right {
        text-align: right;
    }
</style>

<div class="center" style="margin-bottom: 6px;">
    <img src="{{ asset('images/tlogo.png') }}" alt="Logo" style="width: 80px;">
</div>

<div class="center bold" style="font-size: 16px;">ORCAS Betting App</div>
<div class="center bold" style="font-size: 14px;">
    Agent: {{ $agentName ?? 'N/A' }}
</div>

<div class="section">
    <div>Draw Date: <strong>{{ $drawDate ?? 'N/A' }}</strong></div>
    <div>Printed: <strong>{{ $printedTime ?? now()->format('Y-m-d H:i:s') }}</strong></div>
</div>

<div class="divider"></div>

@if (!empty($grouped) && is_array($grouped))
  @foreach ($grouped as $groupKey => $bets)
    @php
      [$gameType, $drawTime] = explode('-', $groupKey);
      $formattedDraw = match((int) $drawTime) {
          14 => '2PM',
          17 => '5PM',
          21 => '9PM',
          default => $drawTime
      };
    @endphp

    <div class="bold mt-2 text-lg">
        {{ strtoupper($gameType) }} — Draw: {{ $formattedDraw }}
    </div>

    <table>
      @foreach ($bets as $bet)
        <tr>
          <td>{{ $bet->bet_number }}</td>
          <td class="right">₱{{ number_format($bet->amount, 2) }}</td>
        </tr>
      @endforeach
    </table>
  @endforeach
@else
  <p>No bets found.</p>
@endif

<div style="display: flex; justify-content: space-between;" class="bold">
    <div style="font-size: 15px;">TOTAL:</div>
    <div style="font-size: 15px;">₱{{ number_format($totalAmount ?? 0, 2) }}</div>
</div>

<div class="divider"></div>

<div class="center section">
    <div class="qr" style="margin-top: 6px;">
        @if (!empty($qrCodeImage))
            <img src="data:image/png;base64,{{ $qrCodeImage }}" width="100">
        @else
            <span style="font-size: 12px; color: red;">QR not available</span>
        @endif
    </div>
    <div style="font-size: 13px;">Multi-Stub Preview</div>
</div>

<div style="height: 60px;"></div>

<script>
    window.print();
</script>
@endsection
