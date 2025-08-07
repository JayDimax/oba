@extends('layouts.admin')

@section('title', 'Sales Reports')

@section('content')
<div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow mb-6">

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Sales Reports</h1>

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
                @if (abs($row['difference']) < 0.01)
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

{{-- FILTER for reports --}}

<form method="GET" action="{{ route('admin.reports.index') }}" class="mb-4 dark:text-white">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <select name="agent_id" class="dark:bg-gray-700 dark:text-white p-2 rounded border">
            <option value="">All Agents</option>
            @foreach($agents as $agent)
            <option value="{{ $agent->id }}" {{ $selectedAgentId == $agent->id ? 'selected' : '' }}>
                {{ $agent->name ?? 'No User' }} ({{ $agent->agent_code ?? 'No Code' }})
            </option>
            @endforeach
        </select>


        <input type="date" name="filter_date" value="{{ $filterDate }}"
            class="form-input dark:bg-gray-800 dark:border-gray-600 dark:text-white" />

        <input type="text" name="stub_id" placeholder="Stub ID" value="{{ $filterStubId }}"
            class="form-input dark:bg-gray-800 dark:border-gray-600 dark:text-white" />

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Filter
        </button>
    </div>
</form>
<hr class="border -mt-1 border-blue-600">
@if($stubs->count())
<div class="overflow-x-auto dark:text-white">
    <table class="min-w-full bg-white dark:bg-gray-900 border dark:border-gray-700">
        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
            <tr>
                <th class="px-4 py-3 border dark:border-gray-700 font-semibold uppercase text-xs">#</th>
                <th class="px-4 py-3 border dark:border-gray-700 font-semibold uppercase text-xs">Stub ID</th>
                <th class="px-4 py-3 border dark:border-gray-700 font-semibold uppercase text-xs">Agent Name</th>
                <th class="px-4 py-3 border dark:border-gray-700 font-semibold uppercase text-xs">Bet Amount</th>
                <!-- <th class="px-4 py-3 border dark:border-gray-700 font-semibold uppercase text-xs">Total Bets</th> -->
                <th class="px-4 py-3 border dark:border-gray-700 font-semibold uppercase text-xs">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stubs as $stub)
            <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                <td class="px-4 py-2 border dark:border-gray-700">{{ $loop->iteration }}</td>
                <td class="px-4 py-2 border dark:border-gray-700">{{ $stub->stub_id }}</td>
                <td class="px-4 py-2 border dark:border-gray-700">
                    {{ $agents->firstWhere('id', $stub->agent_id)?->name ?? 'N/A' }}
                </td>
                <td class="px-4 py-2 border dark:border-gray-700">₱{{ number_format($stub->total_amount, 2) }}</td>
                <!-- <td class="px-4 py-2 border dark:border-gray-700">{{ $stub->total_bets }}</td> -->
                <td class="px-4 py-2 border dark:border-gray-700">
                    <span class="block text-sm">{{ \Carbon\Carbon::parse($stub->latest_game_date)->format('M d, Y') }}</span>
                    <span class="text-xs text-gray-500">({{ \Carbon\Carbon::parse($stub->latest_game_date)->diffForHumans() }})</span>
                </td>


            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="text-center my-4">
<a href="{{ route('admin.reports.index', array_merge(request()->all(), ['print' => 'yes'])) }}" 
target="_blank" 
   class="inline-flex items-center  mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-md transition duration-300 ease-in-out">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2"
         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-4 0h-4v4h4v-4z" />
    </svg>

    Print Report
</a>
</div>
<div class="mt-4">
    {{ $stubs->links() }}
</div>
@else
<p class="mt-4 dark:text-white">No data found for the selected filters.</p>
@endif



















{{-- Print styles --}}
<style>
    @media print {
        body * {
            visibility: hidden;
        }

        .p-4.bg-white.rounded-lg.shadow,
        .p-4.bg-white.rounded-lg.shadow * {
            visibility: visible;
        }

        .p-4.bg-white.rounded-lg.shadow {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        /* Hide filter form and print button on print */
        form,
        button[onclick="window.print()"] {
            display: none !important;
        }
    }
</style>

@endsection