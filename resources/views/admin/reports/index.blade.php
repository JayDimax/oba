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
        <button type="submit" class="flex items-center gap-2 px-4 py-2 mb-2 border border-transparent rounded shadow transition duration-200
            bg-blue-600 hover:bg-blue-700 text-white
            dark:bg-blue-700 dark:hover:bg-blue-800 dark:text-white">
            
            <!-- Lucide Print Icon (Inline SVG) -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8z" />
            </svg>
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
<div class="bg-white dark:bg-gray-800 p-4 rounded shadow my-6">
    <h2 class="text-lg font-semibold text-purple-700 dark:text-purple-300 mb-4">Income Calendar</h2>
    <div id="calendar">
    {{-- Optional summary below calendar: show gross and net by date --}}
    <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
       @php
            use Carbon\Carbon;

            $startDate = Carbon::parse(now()->startOfMonth());
            $endDate = Carbon::parse(now());

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $dateStr = $date->toDateString();
                $gross = $grossPerDay[$dateStr] ?? 0;
                $net = $netPerDay[$dateStr] ?? 0;

                // Skip display if both are 0 or null
                if (empty($gross) && empty($net)) {
                    continue;
                }
        @endphp

            <div class="p-2 border rounded bg-gray-50 dark:bg-gray-700 mb-2">
                <div class="font-semibold">{{ $date->format('M d, Y (D)') }}</div>
                <div><span class="text-purple-600 font-semibold">Gross:</span> ₱{{ number_format($gross, 2) }}</div>
                <div><span class="text-green-600 font-semibold">Net:</span> ₱{{ number_format($net, 2) }}</div>
            </div>

        @php
            }
        @endphp

    </div>
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
    /* Always use black text for weekday names */
.fc .fc-col-header-cell-cushion {
    color: black !important;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: @json($calendarIncome),
        height: 'auto',
        eventColor: '#6b46c1', // Purple background
        eventTextColor: 'white',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        eventContent: function(info) {
            const { gross, net } = {
                gross: parseFloat(info.event.extendedProps.gross || 0),
                net: parseFloat(info.event.title.replace(/[₱,]/g, '') || 0)
            };

            return {
                html: `
                    <div class="text-xs leading-tight">
                        <div class="font-bold text-white">Net: ₱${net.toLocaleString()}</div>
                        <div class="text-[10px] text-gray-200">Gross: ₱${gross.toLocaleString()}</div>
                    </div>
                `
            };
        }
    });
    calendar.render();
});
</script>


@endsection