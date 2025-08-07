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

    .right {
        text-align: right;
    }

    .left {
        text-align: left;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    td {
        padding: 2px 0;
        font-size: 13px;
    }

    .label {
        text-align: left;
    }

    .value {
        text-align: right;
    }

    .highlight {
        font-size: 14px;
        font-weight: bold;
        color: #d00;
    }

    .small {
        font-size: 12px;
    }

    .hidden {
        display: none;
    }

</style>
<div class="center" style="margin-bottom: 6px;">
    <img src="{{ asset('images/tlogo.png') }}" alt="Logo"
         style="width: 80px; max-width: 100%; display: inline-block;">
</div>
{{-- HEADER --}}
<div class="center bold" style="font-size: 16px;">ORCAS Betting App</div>

@if(isset($agentName))
    <div class="center bold" style="font-size: 14px;">Agent: {{ $agentName }}</div>
@endif

@if(isset($drawDate))
    <div class="center small">Draw Date: <strong>{{ $drawDate }}</strong></div>
@endif

@if(isset($draw))
    <div class="center small">
        Draw: <strong>
            {{ $draw === '1st' ? '1st Draw' : ($draw === '2nd' ? '2nd Draw' : ($draw === '3rd' ? '3rd Draw' : 'All Draws') ) }}
        </strong>
    </div>
@endif

<div class="divider"></div>

{{-- SUMMARY BREAKDOWN --}}
<table>
    @php
        $rows = [
            'Gross Sales:'              => $summary['gross'] ?? 0,
            'Hits:'                     => $summary['hits'] ?? 0,
            'Net Sales:'                => $summary['net_sales'] ?? 0,
            'Payout Coverage (Tapada):' => $summary['payouts'] ?? 0,
            'Commission:'         => $summary['commission_base'] ?? 0,
            'Incentives:'              => $summary['incentives'] ?? 0,
        ];

        if ($draw === null || $draw === 'all') {
            $rows['Deductions:'] = $summary['deductions'] ?? 0;
        }
    @endphp

    @foreach ($rows as $label => $value)
        <tr>
            <td class="label">{{ $label }}</td>
            <td class="value">₱{{ number_format($value, 2) }}</td>
        </tr>
    @endforeach
</table>

<div class="divider"></div>

{{-- TOTAL NET REMITTANCE --}}
<table>
    <tr>
        <td class="label bold">Total Net (Remittance):</td>
        <td class="value highlight">₱{{ number_format($summary['net_after_payouts'] ?? 0, 2) }}</td>
    </tr>
</table>

<div class="divider"></div>

{{-- FOOTER --}}
@if(isset($printedTime))
    <div class="center small">Printed: {{ $printedTime }}</div>
@endif

<div style="height: 50px;"></div>

<script>
    window.print();
</script>
@endsection
