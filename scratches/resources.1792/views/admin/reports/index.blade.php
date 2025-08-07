@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Reports</h1>

    {{-- Tabs --}}
    <div class="mb-4 flex space-x-4 border-b border-gray-300 dark:border-gray-700">
        <a href="{{ route('admin.reports.index', ['tab' => 'daily']) }}"
           class="pb-2 border-b-2 {{ $tab === 'daily' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-500' }}">
           Daily
        </a>
        <a href="{{ route('admin.reports.index', ['tab' => 'weekly']) }}"
            class="pb-2 border-b-2 {{ $tab === 'weekly' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-500' }}">
            Weekly
        </a>
        <a href="{{ route('admin.reports.index', ['tab' => 'monthly']) }}"
           class="pb-2 border-b-2 {{ $tab === 'monthly' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-500' }}">
           Monthly
        </a>
        <a href="{{ route('admin.reports.index', ['tab' => 'yearly']) }}"
           class="pb-2 border-b-2 {{ $tab === 'yearly' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-500' }}">
           Yearly
        </a>
    </div>
 
    {{-- Filter Form --}}
   <form action="{{ route('admin.reports.print') }}" method="GET" target="_blank" class="mt-4 text-right">
    <input type="hidden" name="type" value="{{ $tab }}">
    @if ($tab === 'daily')
        <input type="hidden" name="value" value="{{ $filterDate }}">
    @elseif ($tab === 'weekly')
        <input type="hidden" name="value" value="{{ $filterWeek }}">
    @elseif ($tab === 'monthly')
        <input type="hidden" name="value" value="{{ $filterMonth }}">
    @elseif ($tab === 'yearly')
        <input type="hidden" name="value" value="{{ $filterYear }}">
    @endif

    <button type="submit" class="px-4 py-2 mb-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Print Report
    </button>
</form>



   @php
        $filterType = request('type', 'daily'); // default to daily if not set
        $filterValue = match($filterType) {
            'daily' => request('date'),
            'weekly' => request('filter_week'),
            'monthly' => request('month'),
            'yearly' => request('year'),
            default => null,
        };
    @endphp

    {{-- Print Button --}}
    {{-- <div class="mb-4">
        <a href="{{ route('admin.reports.print', ['type' => $filterType, 'value' => $filterValue]) }}"
            target="_blank"
            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                Print Report
        </a>
    </div> --}}
 

    {{-- Report Table --}}
    <table class="w-full table-auto border border-gray-300 dark:border-gray-700 rounded overflow-hidden text-sm">
        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold border-b border-gray-300 dark:border-gray-600">
            <tr>
                <th class="px-4 py-2 text-left">Agent Name</th>
                <th class="px-4 py-2 text-center">Gross Sales</th>
                <th class="px-4 py-2 text-center">Net Remittance</th>
                <th class="px-4 py-2 text-center">Balance</th>
                <th class="px-4 py-2 text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reportData as $row)
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900">
                    <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $row['agent']->name }}</td>
                    <td class="px-4 py-2 text-center text-gray-900 dark:text-gray-100">{{ number_format($row['gross_sales'], 2) }}</td>
                    <td class="px-4 py-2 text-center text-gray-900 dark:text-gray-100">{{ number_format($row['net_remittance'], 2) }}</td>
                    <td class="px-4 py-2 text-center text-gray-900 dark:text-gray-100">{{ number_format($row['difference'], 2) }}</td>
                    <td class="px-4 py-2 text-center">
                        @if ($row['status'] === 'Balanced')
                            <span class="text-green-600 font-semibold">Balanced</span>
                        @else
                            <span class="text-red-600 font-semibold">Under</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">No data found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
</div>

{{-- Print styles --}}
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .p-4.bg-white.rounded-lg.shadow, .p-4.bg-white.rounded-lg.shadow * {
        visibility: visible;
    }
    .p-4.bg-white.rounded-lg.shadow {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    /* Hide filter form and print button on print */
    form, button[onclick="window.print()"] {
        display: none !important;
    }
}
</style>

@endsection
