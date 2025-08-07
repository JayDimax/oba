@extends('layouts.thermalprinter')

@section('content')
  <div class="bg-gray-50 dark:bg-gray-800 rounded-lg shadow px-4 py-3 min-h-32 flex flex-col justify-between">
    <h2 class="text-lg font-semibold text-gray-700 dark:text-white">
      Summary 
      <span class="text-xs text-gray-500">
        ({{ $draw === '1st' ? '1st Draw' : ($draw === '2nd' ? '2nd Draw' : ($draw === '3rd' ? '3rd Draw' : 'All')) }})
      </span>
    </h2>

    <div class="space-y-1 text-sm">
      <div class="flex justify-between">
        <span>Gross Sales:</span>
        <span>₱{{ number_format($summary['gross'], 2) }}</span>
      </div>
      <div class="flex justify-between">
        <span>Hits:</span>
        <span>₱{{ number_format($summary['hits'], 2) }}</span>
      </div>
      <div class="flex justify-between">
        <span>Net Sales:</span>
        <span>₱{{ number_format($summary['net_sales'], 2) }}</span>
      </div>
      <div class="flex justify-between">
        <span>Payout Coverage (Tapada):</span>
        <span>₱{{ number_format($summary['payouts'], 2) }}</span>
      </div>
      <div class="flex justify-between">
        <span>Commission (10%):</span>
        <span>₱{{ number_format($summary['commission_base'], 2) }}</span>
      </div>
      <div class="flex justify-between">
        <span>Incentives:</span>
        <span>₱{{ number_format($summary['incentives'], 2) }}</span>
      </div>
      @if ($draw === null || $draw === 'all')
        <div class="flex justify-between">
          <span>Deductions:</span>
          <span>₱{{ number_format($summary['deductions'], 2) }}</span>
        </div>
      @endif
    </div>

    <hr class="my-2 border-gray-300 dark:border-gray-700">

    <!-- Remittance -->
    <div class="flex justify-between font-bold text-blue-600">
      <div>
        <span class="text-gray-700 dark:text-gray-300 text-sm">
          Total Net (Remittance)
        </span>
        <br>
      </div>
      <span class="text-right text-red-600 italic">
        ₱{{ number_format($summary['net_after_payouts'], 2) }}
      </span>
    </div>

    <!-- Print Button -->
    <div class="flex justify-end pt-3">
      <a 
        href="" 
        onclick="window.print(); return false;"
        class="inline-flex items-center text-sm text-blue-600 hover:underline font-medium"
      >
        🖨️ Print
      </a>
    </div>
  </div>

  <style>
    @media print {
      body * {
        visibility: hidden !important;
      }
      .bg-gray-50, .bg-gray-50 * {
        visibility: visible !important;
      }
      .bg-gray-50 {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 20px;
      }
    }
  </style>
@endsection