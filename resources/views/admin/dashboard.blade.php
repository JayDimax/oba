@extends('layouts.admin')

@section('content')
<div class="px-4 sm:px-6 py-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    {{-- Header and Summary --}}
    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Admin Dashboard
        </h1>

        <div class="w-full md:w-1/2 lg:w-1/3 bg-gray-100 dark:bg-gray-700 p-4 rounded shadow">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Net Sales</h2>
                <p class="text-3xl text-yellow font-semibold text-right">
                    ₱{{ isset($netSales) ? number_format($netSales, 2) : '0.00' }}
                </p>
            </div>
        </div>
    </div>

        {{-- Gross Sales Summary --}}
    <div class="w-full mb-6">
        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded shadow">
            <div class="text-center text-gray-800 dark:text-yellow-400 text-2xl">
                L2 Gross: ₱{{ number_format($l2Gross ?? 0, 2) }} &nbsp;|&nbsp; 
                S3 Gross: ₱{{ number_format($s3Gross ?? 0, 2) }} &nbsp;|&nbsp; 
                4D Gross: ₱{{ number_format($d4Gross ?? 0, 2) }}
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">

        {{-- Today's Summary --}}
        <section class="w-full bg-white dark:bg-gray-800 p-4 rounded shadow">
            <h2 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Today's Summary</h2>

            <div class="flex justify-between text-gray-800 dark:text-gray-300">
                <span>Total Gross Bets:</span>
                <span>₱{{ isset($todaySummary['gross']) ? number_format($todaySummary['gross'], 2) : '0.00' }}</span>
            </div>

            <div class="flex justify-between text-gray-800 dark:text-gray-300">
                <span>Total Winnings:</span>
                <span>₱{{ isset($todaySummary['totalWinnings']) ? number_format($todaySummary['totalWinnings'], 2) : '0.00' }}</span>
            </div>

            <div class="flex justify-between text-gray-800 dark:text-gray-300">
                <span>Expected Remittance:</span>
                <span>₱{{ isset($todaySummary['computed']) ? number_format($todaySummary['computed'], 2) : '0.00' }}</span>
            </div>

            <div class="flex justify-between text-gray-800 dark:text-gray-300">
                <span>Actual Remittance:</span>
                <span>₱{{ isset($actualRemittance) ? number_format($actualRemittance, 2) : '0.00' }}</span>
            </div>

            <div class="flex justify-between text-red-600 dark:text-yellow-400">
                <span>Unremitted Balance:</span>
                <span>
                    ₱{{ isset($unremittedBalance) ? number_format($unremittedBalance, 2) : '0.00' }}
                </span>
            </div>
        </section>


        {{-- Agent Status --}}
        <section class="w-full bg-white dark:bg-gray-800 p-4 rounded shadow">

            <h2 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Agent Status</h2>
            <div class="flex justify-between text-gray-800 dark:text-gray-300"><span>Total Agents:</span><span>{{ isset($totalAgents) ? $totalAgents : 'N/A'}}</span></div>
            <div class="flex justify-between text-gray-800 dark:text-gray-300"><span>Active Agents:</span><span>{{ isset($activeAgents) ? $activeAgents : 'N/A'}}</span></div>
            <div class="flex justify-between text-gray-800 dark:text-gray-300"><span>Blocked Agents:</span><span>{{ isset($blockedAgents) ? $blockedAgents : 'N/A'}}</span></div>
            <div class="flex justify-between text-red-600 dark:text-yellow-400"><span>Agents With Balance:</span><span>{{ isset($agentsWithBalance) ? $agentsWithBalance : 'N/A' }}</span></div>
        </section>

            {{-- All Draws --}}
            @php
                $drawTimes = [
                    '14' => '2PM',
                    '17' => '5PM',
                    '21' => '9PM',
                ];
                $gameTypes = ['L2', 'S3', '4D'];
            @endphp

            <section class="w-full bg-white dark:bg-gray-800 p-4 rounded shadow">
                <h2 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Draw Status</h2>

                {{-- Next Draw --}}
                <div class="flex justify-between text-gray-800 dark:text-gray-300 mb-2">
                    <span>Next Draw:</span>
                    <span>{{ $nextDraw['label'] ?? 'No more draws today' }}</span>
                </div>

                {{-- Results per Draw --}}
                @foreach($drawTimes as $time => $label)
                    <div class="flex justify-between items-start text-gray-800 dark:text-gray-300 mb-1">
                        {{-- Left: Label --}}
                        <span class="font-semibold w-1/3">{{ $label }}:</span>

                        {{-- Right: All Game Types --}}
                        <span class="w-2/3 text-right">
                            @foreach($gameTypes as $type)
                                @php
                                    $match = ($allResults ?? collect())->firstWhere(function ($result) use ($time, $type) {
                                        return $result->game_draw == $time && $result->game_type == $type;
                                    });
                                @endphp
                                {{ $type }} -
                                @if ($match)
                                    {{ $match->winning_combination }}
                                @else
                                    <span class="italic text-sm text-yellow-600 dark:text-yellow-400">Pending</span>
                                @endif
                                @if (!$loop->last)
                                    <span class="mx-1">|</span>
                                @endif
                            @endforeach
                        </span>
                    </div>
                @endforeach
            </section>



    </div>





    {{-- deficit --}}
    @if(isset($deficit) && $deficit > 0)

    <section class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-800 dark:text-black p-4 rounded shadow col-span-1 md:col-span-2 lg:col-span-3">
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-red-600 dark:text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z" />
            </svg>
            <h2 class="font-semibold text-lg dark:text-white">System Deficit Detected</h2>
        </div>
        <p class="text-sm dark:text-white">
            The system has recorded a deficit for today. Gross sales are not enough to cover total winnings and incentives.
        </p>
        <p class="mt-2 text-base font-semibold dark:text-white">
            Deficit Amount: ₱{{ number_format(abs($deficit), 2) }}
        </p>
    </section>
    @endif

    {{-- Bets Report Section --}}
    <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow mt-10">

 
        {{-- Top 3 Combinations per Game Type --}}
        <div class="flex flex-col">
            <h3 class="text-md font-bold text-center text-gray-900 dark:text-white mb-3 ">
                Top 3 Combinations Today
            </h3>

            <div class="flex flex-wrap gap-4">
                @foreach (['L2' => '🎯', 'S3' => '🎰', '4D' => '🔢'] as $type => $icon)
                <div class="flex-1 min-w-[150px] max-w-full bg-white dark:bg-gray-800 p-4 rounded shadow">
                    <h4 class="text-md font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <span class="text-lg">{{ $icon }}</span>
                        <span>{{ $type }}</span>
                    </h4>

                    @if (isset($topCombinations[$type]) && $topCombinations[$type]->count())
                    @foreach ($topCombinations[$type] as $bet)
                    <div class="bg-gray-200 dark:bg-gray-700 px-3 py-1 mb-1 rounded font-mono text-center">
                        {{ $bet->bet_number }}
                        <span class="text-xs font-semibold">×{{ $bet->total }}</span>
                    </div>
                    @endforeach
                    @else
                    <div class="text-gray-400 text-sm">No data</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    {{-- FILTER for reports --}}
    <hr class="border border-blue-600 dark:border-blue-400 mt-10 mb-4">

    <form method="GET" action="{{ route('admin.admindashboard') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
        <div>
            <label class="block text-gray-700 dark:text-gray-200">From Date</label>
            <input type="date" name="from_date" value="{{ old('from_date', $from ?? request('from_date', today()->toDateString())) }}"
                class="w-full mt-1 p-2 border rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
        </div>

        <div>
            <label class="block text-gray-700 dark:text-gray-200">To Date</label>
            <input type="date" name="to_date" value="{{ request('to_date', $to ?? \Carbon\Carbon::today()->toDateString()) }}"
                class="w-full mt-1 p-2 border rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
        </div>

        <div>
            <label class="block text-gray-700 dark:text-gray-200">Draw Time</label>
            <select name="draw_time" class="w-full mt-1 p-2 border rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
                <option value="ALL" {{ request('draw_time', 'ALL') === 'ALL' ? 'selected' : '' }}>All</option>
                <option value="2PM" {{ request('draw_time') === '2PM' ? 'selected' : '' }}>2PM</option>
                <option value="5PM" {{ request('draw_time') === '5PM' ? 'selected' : '' }}>5PM</option>
                <option value="9PM" {{ request('draw_time') === '9PM' ? 'selected' : '' }}>9PM</option>
            </select>
        </div>

        <div>
            <label class="block text-gray-700 dark:text-gray-200">Agent Name</label>
            <input type="text" name="agent_name" value="{{ request('agent_name') }}"
                placeholder="Search agent..."
                class="w-full mt-1 p-2 border rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
        </div>

        <div class="md:col-span-4 flex gap-3 mt-2">
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Apply Filters
            </button>
            <a href="{{ route('admin.admindashboard') }}"
            class="px-4 py-2 bg-gray-300 dark:bg-gray-700 dark:text-white text-gray-800 rounded hover:bg-gray-400 dark:hover:bg-gray-600 transition">
                Reset
            </a>
        </div>
    </form>
    
    {{-- Active Filters --}}
    @if(request()->anyFilled(['from_date', 'to_date', 'draw_time', 'agent_name']))
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        <strong>Active Filters:</strong>
        @if(request('from_date')) From: {{ request('from_date') }} @endif
        @if(request('to_date')) To: {{ request('to_date') }} @endif
        @if(request('draw_time')) Draw: {{ request('draw_time') }} @endif
        @if(request('agent_name')) Agent: {{ request('agent_name') }} @endif

        <a href="{{ route('admin.admindashboard') }}"
            class="ml-2 text-blue-500 hover:underline">Reset</a>
    </div>
    @endif

    {{-- Bets Table --}}
    <div class="overflow-x-auto">
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
                    <td class="px-4 py-2">{{ \Carbon\Carbon::createFromTime($bet->game_draw)->format('g:i A') }}</td>
                    <td class="px-4 py-2">{{ $bet->game_type }}</td>
                    <td class="px-4 py-2">{{ $bet->bet_number }}</td>
                    <td class="px-4 py-2 text-right">₱{{ number_format($bet->amount, 2) }}</td>
                    <td class="px-4 py-2 text-center">{{ $bet->game_date ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">
                        No bets found for this filter.
                    </td>
                </tr>
                @endforelse 
                @endisset
            </tbody>
        </table>
    </div>
    <div class="mt-4"> 
        @if(isset($bets) && $bets instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{ $bets->withQueryString()->links() }}
        @else
            <p>No data available or session expired.</p>
        @endif
    </div>

    {{-- Print Button --}}
    <div class="text-center my-4 flex justify-center gap-4">
        {{-- Print Button --}}
        <a href="{{ route('admin.admindashboard', array_merge(request()->all(), ['print' => 'yes'])) }}"
        target="_blank"
        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-md transition duration-300 ease-in-out">
            🖨 Print Report
        </a>
 
        {{-- Export Button --}}
        <a href="{{ route('admin.export-bets', request()->all()) }}"
        class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black text-sm font-medium rounded-lg shadow-md transition duration-300 ease-in-out">
            📥 Export to Excel
        </a>
    </div>


</div>




{{-- Print Styles --}}
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

        form,
        button,
        nav,
        .no-print {
            display: none !important;
        }
    }
</style>
@endsection