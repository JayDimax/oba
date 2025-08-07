@extends('layouts.admin')

@section('content')
<div class="px-4 sm:px-6 py-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Admin Dashboard</h1>

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
                <span>₱{{ isset($todaySummary['final']) ? number_format($todaySummary['final'], 2) : '0.00' }}</span>
            </div>

            <div class="flex justify-between text-gray-800 dark:text-gray-300">
                <span>Actual Remittance:</span>
                <span>₱{{ isset($actualRemittance) ? number_format($actualRemittance, 2) : '0.00' }}</span>
            </div>

            <div class="flex justify-between text-red-600 dark:text-red-400">  
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
            <div class="flex justify-between text-red-600 dark:text-red-400"><span>Agents With Balance:</span><span>{{ isset($agentsWithBalance) ? $agentsWithBalance : 'N/A' }}</span></div>
        </section>


        {{-- Draw Status --}}
       <section class="w-full bg-white dark:bg-gray-800 p-4 rounded shadow">
            <h2 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Draw Status</h2>

            {{-- Next Draw --}}
            <div class="flex justify-between text-gray-800 dark:text-gray-300 mb-2">
                <span>Next Draw:</span>
                <span>{{ $nextDraw['label'] ?? 'No more draws today' }}</span>
            </div>

            {{-- All Draws --}}
            @php
            $drawLabels = [
                '14' => '2PM Draw',
                '17' => '5PM Draw',
                '21' => '9PM Draw',
            ];
            @endphp

            @foreach($drawLabels as $time => $label)
                @php
                    $matchingResult = $allResults->firstWhere('game_draw', $time);
                @endphp
                <div class="flex justify-between text-gray-800 dark:text-gray-300">
                    <span>{{ $label }}:</span>
                    <span>
                        @if($matchingResult)
                            {{ $matchingResult->game_type }} - {{ $matchingResult->winning_combination }}
                        @else
                            Pending
                        @endif
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


        {{-- Summary --}}
        <div class="flex justify-end mb-6">
            <div class="w-full md:w-1/2 lg:w-1/3 bg-gray-100 dark:bg-gray-700 p-4 rounded shadow">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Net Sales</h2>
                    <p class="text-3xl text-yellow font-semibold text-right">₱{{ isset($netSales) ? number_format($netSales, 2) : '0:00' }}</p>
                </div>
            </div>
        </div>
        {{-- Top 3 Combinations per Game Type --}}
        <div class="flex flex-col">
            <h3 class="text-md font-bold text-center text-gray-900 dark:text-white mb-3">
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
        .mb-4.flex,
        nav {
            display: none !important;
        }
    }
</style>
@endsection