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

<hr class="border -mt-1 border-blue-600">
{{-- filter for reports --}}
<div>
    {{-- Filter Form --}}
    <form method="GET" action="{{ route('admin.reports.index') }}" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- From Date --}}
            <div>
                <label for="from_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Date</label>
                <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}"
                    class="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm">
            </div>

            {{-- To Date --}}
            <div>
                <label for="to_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Date</label>
                <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}"
                    class="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm">
            </div>

            {{-- Draw Time --}}
            <div>
                <label for="draw_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Draw Time</label>
                <select name="draw_time" id="draw_time"
                    class="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm">
                    <option value="ALL" {{ request('draw_time') == 'ALL' ? 'selected' : '' }}>All</option>
                    <option value="2PM" {{ request('draw_time') == '2PM' ? 'selected' : '' }}>2PM</option>
                    <option value="5PM" {{ request('draw_time') == '5PM' ? 'selected' : '' }}>5PM</option>
                    <option value="9PM" {{ request('draw_time') == '9PM' ? 'selected' : '' }}>9PM</option>
                </select>
            </div>

            {{-- Agent Selector --}}
            <div>
                <label for="agent_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Agent</label>
                <select name="agent_name" id="agent_name"
                    class="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm">
                    <option value="">All Agents</option>
                    @isset($agents)
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}" {{ request('agent_name') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }} ({{ $agent->user->agent_code ?? 'No Code' }})
                            </option>
                        @endforeach
                    @endisset
                </select>
            </div>

        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit"
                class="inline-flex items-center px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded shadow hover:bg-blue-700 transition">
                🔍 Filter
            </button>
        </div>
    </form>



    {{-- Active Filters Tag --}}
    @if(request()->anyFilled(['from_date', 'to_date', 'draw_time', 'agent_name']))
        <div class="mb-4 text-sm text-gray-700 dark:text-gray-300">
            <strong>Active Filters:</strong>
            @if(request('from_date')) <span class="ml-2">From: {{ request('from_date') }}</span> @endif
            @if(request('to_date')) <span class="ml-2">To: {{ request('to_date') }}</span> @endif
            @if(request('draw_time')) <span class="ml-2">Draw: {{ request('draw_time') }}</span> @endif
            @if(request('agent_name')) <span class="ml-2">Agent: {{ request('agent_name') }}</span> @endif
            <a href="{{ route('admin.reports.index') }}" class="ml-4 text-blue-600 hover:underline">Reset</a>
        </div>
    @endif

    {{-- Print Button --}}
    <div class="mb-4 flex justify-end">
        <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">
            🖨️ Print Table
        </button>
    </div>

    {{-- Data Table --}}
    <div class="overflow-x-auto rounded shadow">
        <table class="min-w-full table-auto border border-gray-300 dark:border-gray-700 text-sm">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold">
                <tr>
                    <th class="px-4 py-2 text-left">Agent Code</th>
                    <th class="px-4 py-2 text-left">Game Time</th>
                    <th class="px-4 py-2 text-left">Game Type</th>
                    <th class="px-4 py-2 text-left">Bet #</th>
                    <th class="px-4 py-2 text-right">Bet Amount</th>
                    <th class="px-4 py-2 text-center">Date</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 dark:text-gray-100">
                @isset($bets)
                    @forelse ($bets as $bet)
                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900">
                            <td class="px-4 py-2">{{ $bet->betAgent->agent_code ?? '—' }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::createFromTimeString($bet->game_draw)->format('g:i A') }}</td>
                            <td class="px-4 py-2">{{ $bet->game_type }}</td>
                            <td class="px-4 py-2">{{ $bet->bet_number }}</td>
                            <td class="px-4 py-2 text-right">₱{{ number_format($bet->amount, 2) }}</td>
                            <td class="px-4 py-2 text-center">{{ $bet->game_date }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">No bets found for this filter.</td>
                        </tr>
                    @endforelse
                @endisset
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        @if (isset($bets) && $bets instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            {{ $bets->withQueryString()->links() }}
        @endif
    </div>

</div>

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