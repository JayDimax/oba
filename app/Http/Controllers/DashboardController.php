<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bet;
use App\Models\User;
use App\Models\Agent;
use App\Models\Result;
use Carbon\CarbonPeriod;
use App\Models\Collection;
use App\Models\Deduction; 
use App\Models\AgentBalance;
use Illuminate\Http\Request;
use App\Models\CollectionStub;
use App\Models\AgentCommission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\RemittanceCalculator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function admin(Request $request)
    {
        
        /** ─────────────────────────────────────────────
         *  1. BASE DATES & FILTER INPUTS
         *  ────────────────────────────────────────────*/
        $today = today(); // Laravel's Carbon helper
        $now   = now();   // current datetime (Asia/Manila)

        $from       = $request->input('from_date', $today->toDateString());
        $to         = $request->input('to_date', $today->toDateString());
        $drawFilter = $request->input('draw_time', 'ALL');
        $agentName = $request->input('agent_name');

        $drawMap = ['2PM' => '14', '5PM' => '17', '9PM' => '21'];
        $drawTimeValue = $drawFilter !== 'ALL' && isset($drawMap[$drawFilter])
            ? $drawMap[$drawFilter]
            : null;

        /** ─────────────────────────────────────────────
         *  2. CORE QUERY (FILTERED BETS)
         *  ────────────────────────────────────────────*/
        $betsQuery = Bet::with('agent.user')
            ->whereBetween('game_date', [$from, $to])
            ->when($drawTimeValue, fn($q) => $q->where('game_draw', $drawTimeValue))
            ->when($agentName, fn($q) => $q->whereHas('betAgent', fn($subQ) =>
                $subQ->where('name', 'like', "%{$agentName}%")
            ));
        $bets = $betsQuery->orderByDesc('created_at')->paginate(10); 




        // Clone query to avoid interfering with pagination or .get()------------->>>>>>>>>>
        $filteredBets = (clone $betsQuery)->get();
        $filteredGross = $filteredBets->sum('amount');
           
        $agentId = $filteredBets->first()->agent_id ?? null;
        // First, preload commission rates into a key-value pair: [game_type => rate]
        $commissionRate = AgentCommission::where('agent_id', $agentId)
            ->pluck('commission_percent', 'game_type') // returns a collection like ['L2' => 0.1, 'S3' => 0.08, etc.]
            ->toArray();

        // Compute Commission
        $filteredCommission = $filteredBets->sum(function ($bet) use ($commissionRate) {
            if ($bet->is_winner) {
                return 0;
            }

            $rate = $commissionRate[$bet->game_type] ?? 0; // fallback if not found
            return $bet->amount * $rate;
        });



        $filteredWinnings = $filteredBets->sum(function ($bet) {
            return $bet->is_winner ? $bet->winnings ?? 0 : 0;
        });

        // Adjust net sales calculation:
        $filteredNetSales = $filteredBets->sum(function ($bet) use ($commissionRate) {
            $rate = $commissionRate[$bet->game_type] ?? 0; // Default to 0 if missing

            if ($bet->is_winner) {
                // Net is amount minus winnings (no commission)
                return $bet->amount - $bet->winnings;
            } else {
                // Net is amount minus commission
                return $bet->amount - ($bet->amount * $rate);
            }
        });

        $filteredGross      = $filteredTotals->gross ?? 0;
        $filteredCommission = $filteredTotals->commission ?? 0;
        $filteredNetSales   = $filteredTotals->net_sales ?? 0;
        //------------------------------------------------>>>>>>>

       // 20 rows per page



        /** ─────────────────────────────────────────────
         *  3. TODAY'S SUMMARY (UNFILTERED)
         *  ────────────────────────────────────────────*/ 
        $betsToday = Bet::whereDate('game_date', $today)->get();

        $todaySummary = RemittanceCalculator::computeNetRemit($betsToday);

        $totalGross      = $todaySummary['gross'];
        $totalWinnings   = $todaySummary['totalWinnings'];
        $totalCommission = $todaySummary['commission'];
        $totalDeductions = Deduction::whereDate('created_at', $today)->sum('amount');

        $expectedRemittance = $todaySummary['computed'] + $totalDeductions;

        $actualRemittance = Collection::whereDate('created_at', $today)
            ->where('status', 'approved')
            ->sum('net_remit');

        $unremittedBalance = max(0, $expectedRemittance - $actualRemittance);

        $deficit = max(0, $totalWinnings + $totalCommission - ($totalGross + $totalDeductions));
        $netAfterCommission = $totalGross - $totalCommission;
        $netSales = $netAfterCommission - $totalWinnings;

        /** ─────────────────────────────────────────────
         *  4. STATS & NEXT DRAW
         *  ────────────────────────────────────────────*/
        $totalAgents       = User::where('role', 'agent')->count();
        $activeAgents      = User::where('role', 'agent')->where('is_active', true)->count();
        $blockedAgents     = $totalAgents - $activeAgents;
        $agentsWithBalance = AgentBalance::where('amount', '>', 0)->count();

        $nextDraw = collect([
            ['label' => '2PM', 'time' => $today->copy()->setTime(14, 0)],
            ['label' => '5PM', 'time' => $today->copy()->setTime(17, 0)],
            ['label' => '9PM', 'time' => $today->copy()->setTime(21, 0)],
        ])->first(fn($d) => $d['time']->greaterThan($now));

        $latestResult = Result::latest('game_date')->first();
 
        $topCombinations = DB::table('bets')
            ->select('game_type', 'bet_number', DB::raw('COUNT(*) as total'))
            ->whereDate('game_date', $today)
            ->groupBy('game_type', 'bet_number')
            ->orderByDesc('total')
            ->get()
            ->groupBy('game_type')
            ->map(fn($g) => $g->take(5));

        $allResults = Result::whereDate('created_at', today())->get();

        $agents = Agent::orderBy('name')->get();

        // =========[ FILTERS ]=========
        $from = $request->input('from_date', $today->toDateString());
        $to = $request->input('to_date', $today->toDateString());
        $draw = $request->input('draw_time', 'ALL');
        $agentName = $request->input('agent_name');

        $drawTimeMap = ['2PM' => '14', '5PM' => '17', '9PM' => '21'];
        $drawTimeValue = $draw !== 'ALL' && isset($drawTimeMap[$draw]) ? $drawTimeMap[$draw] : $draw;

        $agents = User::where('role', 'agent')->get();
        $agentCodes = User::where('role', 'agent')->pluck('agent_code', 'id');

        // =========[ BETS DATA ]=========
        $bets = Bet::with('agent.user')
        ->when($from, fn($q) => $q->whereDate('game_date', '>=', $from))
        ->when($to, fn($q) => $q->whereDate('game_date', '<=', $to))
        ->when($drawTimeValue !== 'ALL', fn($q) => $q->where('game_draw', $drawTimeValue))
        ->when($agentName, fn($q) => $q->whereHas('betAgent', fn($subQ) => $subQ->where('name', 'like', "%{$agentName}%")))
        ->orderByDesc('created_at')
        ->paginate(10); 

          if ($request->has('print')) {
                return view('admin.bets-print', compact('bets', 'request'));
            }
        $l2Gross = $filteredBets->where('game_type', 'L2')->sum('amount');
        $s3Gross = $filteredBets->where('game_type', 'S3')->sum('amount');
        $d4Gross = $filteredBets->where('game_type', '4D')->sum('amount');

        return view('admin.dashboard', compact(
            // headline
            'todaySummary',
            'totalDeductions',
            'actualRemittance',
            'unremittedBalance',
            'deficit',
            'netSales',
            'allResults',
            'l2Gross',
            's3Gross',
            'd4Gross',
            // stats
            'totalAgents',
            'activeAgents',
            'blockedAgents', 
            'agentsWithBalance',
            'nextDraw',
            'latestResult',
  
            // filters & data
            'bets',
            'from',
            'to',
            'drawFilter',
            'agentName',
            'topCombinations',
            'agents',
   
            // filtered totals (new)
            'filteredGross',
            'filteredCommission',
            'filteredNetSales',
            'filteredWinnings',

     
        ));

    }

    public function exportBets(Request $request)
    {
        $from       = $request->input('from_date');
        $to         = $request->input('to_date');
        $drawFilter = $request->input('draw_time');
        $agentName  = $request->input('agent_name');

        $query = Bet::with('betAgent')
            ->when($from && $to, fn($q) => $q->whereBetween('game_date', [$from, $to]))
            ->when($drawFilter && $drawFilter !== 'ALL', fn($q) => $q->where('game_draw', $drawFilter))
            ->when($agentName, fn($q) => $q->whereHas('betAgent', fn($q2) => $q2->where('name', 'LIKE', "%$agentName%")))
            ->orderByDesc('game_date');

        $columns = ['Agent Code', 'Game Time', 'Game Type', 'Bet #', 'Bet Amount', 'Date'];

        $callback = function () use ($query, $columns) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Add header row
            fputcsv($file, $columns);

            foreach ($query->cursor() as $bet) {
                fputcsv($file, [
                    $bet->betAgent->agent_code ?? '',
                    \Carbon\Carbon::createFromTimeString($bet->game_draw)->format('g:i A'), // e.g., 2:00 PM
                    $bet->game_type,
                    $bet->bet_number,
                    number_format($bet->amount, 2),
                    \Carbon\Carbon::parse($bet->game_date)->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        $filename = 'bets_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
    
    public function store()
    {
        $user = Auth::user();
        return view('dashboard.index', compact('user'));
    } 
    public function cashier()
    {
        $user = Auth::user();
        return view('dashboard.cashier', compact('user'));
    }

    public function agent()
    {

        $user = Auth::user();
        
        return view('dashboard.agent', compact('user'));

    }

    //REPORTS FUNCTIONS ----->
    public function index(Request $request)
    {
            // ─────────────────────────────────────────────
            // 1. Handle Tab & Base Report Data
            // ─────────────────────────────────────────────
            $tab = $request->input('tab', 'daily');
            $reportData = null;
            $filterDate = $filterMonth = $filterWeek = $filterYear = null;
        
            switch ($tab) {
                case 'monthly':
                    $filterMonth = $request->input('filter_month', now()->format('Y-m'));
                    $reportData = $this->getMonthlyReportData($filterMonth);
                    break;
        
                case 'yearly':
                    $filterYear = $request->input('filter_year', now()->format('Y'));
                    $reportData = $this->getYearlyReportData($filterYear);
                    break;
        
                case 'weekly':
                    $filterWeek = $request->input('filter_week', now()->startOfWeek()->format('Y-m-d'));
                    $reportData = $this->getWeeklyReportData($filterWeek);
                    break;
        
                case 'daily':
                default:
                    $filterDate = $request->input('filter_date', now()->toDateString());
                    $reportData = $this->getDailyReportData($filterDate);
                    break;
            }
            $printMode = $request->input('print') === 'yes';
            // ─────────────────────────────────────────────
            // 2. Filters from Request
            // ─────────────────────────────────────────────
            $selectedAgentId = $request->input('agent_id');
            $filterStubId = $request->input('stub_id');
            $filterDate = $request->input('filter_date');
            // ─────────────────────────────────────────────
            // 3. Fetch Agents
            // ─────────────────────────────────────────────
            $agents = User::where('role', 'agent')
            ->select('id', 'name','agent_code')
            ->get();
        
            // ─────────────────────────────────────────────
            // 4. Build Grouped Stub Report
            // ─────────────────────────────────────────────
            $stubQuery = DB::table('bets')
            ->join('collection_stub', 'bets.stub_id', '=', 'collection_stub.stub_id')
            ->join('collections', 'collection_stub.collection_id', '=', 'collections.id')
            ->select(
                'bets.stub_id',
                'bets.agent_id',
                DB::raw('SUM(bets.amount) as total_amount'),
                DB::raw('COUNT(*) as total_bets'),
                DB::raw('MAX(bets.game_date) as latest_game_date')
            )
            ->where('collections.status', 'approved')
            ->when($selectedAgentId, fn($query, $agentId) => $query->where('bets.agent_id', $agentId))
            ->when($filterDate, fn($query) => $query->whereDate('bets.created_at', Carbon::parse($filterDate)->toDateString()))
            ->when($filterStubId, fn($query) => $query->where('bets.stub_id', $filterStubId))
            ->groupBy('bets.stub_id', 'bets.agent_id')
            ->orderByDesc('latest_game_date');
            // Use paginate for normal view, get() for print
            $groupedStubs = $printMode ? $stubQuery->get() : $stubQuery->paginate(10);
            $stubIds = collect($groupedStubs)->pluck('stub_id')->toArray();
        
            $representativeBets = Bet::whereIn('stub_id', $stubIds)
                ->orderByDesc('created_at')
                ->get()
                ->keyBy('stub_id');

            // Set current month start and today
            $startOfMonth = now()->startOfMonth();
            $today = now();

            $dataRangeStart = $startOfMonth->toDateString();
            $dataRangeEnd = $today->toDateString();

            // Filters
            $drawFilter = $request->input('draw_time', 'ALL');
            $agentName = $request->input('agent_name');

            $drawMap = ['2PM' => '14', '5PM' => '17', '9PM' => '21'];
            $drawTimeValue = $drawFilter !== 'ALL' && isset($drawMap[$drawFilter])
                ? $drawMap[$drawFilter]
                : null;

            // Query only up to today
            $betsQuery = Bet::with('agent.user')
                ->whereBetween('game_date', [$dataRangeStart, $dataRangeEnd])
                ->when($drawTimeValue, fn($q) => $q->where('game_draw', $drawTimeValue))
                ->when($agentName, fn($q) => $q->whereHas('betAgent', fn($subQ) =>
                    $subQ->where('name', 'like', "%{$agentName}%")
                ));

            $filteredBets = $betsQuery->get();

            $commissionRates = DB::table('agent_commissions')
            ->select('agent_id', 'game_type', 'commission_percent')
            ->get()
            ->keyBy(fn($row) => $row->agent_id . '_' . $row->game_type);

            // Group by game_date


            $dailySummaryMap = $filteredBets
                ->groupBy(fn($bet) => Carbon::parse($bet->game_date)->toDateString())
                ->map(function ($bets, $date) {
                    // Step 1: Fetch commission rates for involved agents and game types
                    $commissionRates = DB::table('agent_commissions')
                        ->whereIn('agent_id', $bets->pluck('agent_id')->unique())
                        ->whereIn('game_type', $bets->pluck('game_type')->unique())
                        ->get()
                        ->keyBy(fn($item) => $item->agent_id . '-' . $item->game_type);

                    // Step 2: Compute values
                    $gross = $bets->sum('amount');

                    $commission = $bets->sum(function ($bet) use ($commissionRates) {
                        if ($bet->is_winner) return 0;

                        $key = $bet->agent_id . '-' . $bet->game_type;
                        $percent = $commissionRates[$key]->commission_percent ?? 10; // fallback to 10% if not found

                        return $bet->amount * ($percent / 100);
                    });

                    $winnings = $bets->sum(fn($bet) => $bet->is_winner ? ($bet->winnings ?? 0) : 0);
                    $netSales = $gross - $commission - $winnings;

                    return [
                        'date' => $date,
                        'gross' => $gross,
                        'commission' => $commission,
                        'winnings' => $winnings,
                        'netSales' => $netSales,
                    ];
                });



                // Loop from August 1 to today only
                $period = CarbonPeriod::create($startOfMonth, $today);
                $calendarIncome = [];

                foreach ($period as $date) {
                    $dateStr = $date->toDateString();
                    $data = $dailySummaryMap->get($dateStr);

                    // Skip if no data or all values are zero
                    if (!$data || ($data['gross'] == 0 && $data['netSales'] == 0)) {
                        continue;
                    }

                    $calendarIncome[] = [
                        'title' => '₱' . number_format($data['netSales'], 2),
                        'start' => $dateStr,
                        'extendedProps' => [
                            'gross' => $data['gross'],
                            'commission' => $data['commission'],
                            'winnings' => $data['winnings'],
                        ],
                    ];
                }
                $grossPerDay = $dailySummaryMap->map(fn($data) => $data['gross'])->all();
                $netPerDay = $dailySummaryMap->map(fn($data) => $data['netSales'])->all();



        return view($printMode ? 'admin.reports.print' : 'admin.reports.index', [
            'tab' => $tab,
            'reportData' => $reportData,
            'filterDate' => $filterDate,
            'filterMonth' => $filterMonth,
            'filterWeek' => $filterWeek,
            'filterYear' => $filterYear,
            'stubs' => $groupedStubs,
            'agents' => $agents,
            'selectedAgentId' => $selectedAgentId,
            'representativeBets' => $representativeBets,
            'startDate' => $filterDate,
            'filterStubId' => $filterStubId,
            'calendarIncome' => $calendarIncome,
            'drawFilter' => $drawFilter,
            'agentName' => $agentName,
            'grossPerDay' => $grossPerDay,
            'netPerDay' => $netPerDay,
            'data' => $reportData,
        ]);
    }


    private function getDailyReportData($filterDate)
    {
        $agents = User::where('role', 'agent')->get();
        $reportData = [];

        foreach ($agents as $agent) {
            $bets = Bet::where('agent_id', $agent->id)
                ->whereDate('created_at', $filterDate)
                ->get();

            $grossSales = $bets->sum('amount');

            // Winnings: amount * multiplier
            $winnings = $bets->where('is_winner', 1)->sum(function ($bet) {
                return $bet->amount * (optional($bet->multiplier)->multiplier ?? 0);
            });

            // Payouts = Winnings (can be separated if needed)
            $payouts = $winnings;

            // Deductions: fetched directly from deductions table
            $deductions = DB::table('deductions')
                ->where('agent_id', $agent->id)
                ->whereDate('created_at', $filterDate)
                ->sum('amount') ?? 0;

            // Commission: 10% default of gross
            $commission = $grossSales * 0.10;

            $payouts = $bets->where('is_winner', 1)->sum(function ($bet) {
                return $bet->amount * $bet->multiplier; // Already includes incentives
            });

            // Net Sales logic
            if ($payouts > 0) {
                $netSales = $grossSales - $commission - $payouts + $deductions;
                
            } else {
                $netSales = $grossSales - $commission + $deductions;
            }

            $netRemittance = Collection::where('agent_id', $agent->id)
                ->whereDate('created_at', $filterDate)
                ->sum('net_remit');

            $difference = $netRemittance - $netSales;

            $reportData[] = [
                'agent' => $agent,
                'gross_sales' => $grossSales,
                'net_remittance' => $netRemittance,
                'net_sales' => $netSales,
                'difference' => $difference,
                'status' => abs($difference) < 0.01 ? 'Balanced' : 'Under',

            ];
        }

        return $reportData;
    }

    protected function getWeeklyReportData($startOfWeek)
    {
        $start = Carbon::parse($startOfWeek)->startOfWeek(); // Monday
        $end = (clone $start)->endOfWeek(); // Sunday

        $agents = \App\Models\User::where('role', 'agent')->get(); // or assigned agents only
        $reportData = [];

        foreach ($agents as $agent) {
            // Get all bets this week
            $bets = \App\Models\Bet::where('agent_id', $agent->id)
                ->whereBetween('created_at', [$start, $end])
                ->get();

            $grossSales = $bets->sum('amount');

            // Winnings
            $winnings = $bets->where('is_winner', 1)->sum(function ($bet) {
                return $bet->amount * (optional($bet->multiplier)->multiplier ?? 0);
            });

            $payouts = $winnings;



            // Deductions from `deductions` table
            $deductions = DB::table('deductions')
                ->where('agent_id', $agent->id)
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount') ?? 0;

            // Commission
            $commission = $grossSales * 0.10;

                   $payouts = $bets->where('is_winner', 1)->sum(function ($bet) {
                return $bet->amount * $bet->multiplier; // Already includes incentives
            });

            // Net Sales logic
            if ($payouts > 0) {
                $netSales = $grossSales - $commission - $payouts + $deductions;

            } else {
                $netSales = $grossSales - $commission + $deductions;
            }

            // Net Remittance (only approved)
            $netRemit = \App\Models\Collection::where('agent_id', $agent->id)
                ->where('status', 'approved')
                ->whereBetween('created_at', [$start, $end])
                ->sum('net_remit');

            $difference = $netRemit - $netSales;

            $reportData[] = [
                'agent' => $agent,
                'gross_sales' => $grossSales,
                'net_remittance' => $netRemit,
                'net_sales' => $netSales,
                'difference' => $difference,
                'status' => abs($difference) < 0.01 ? 'Balanced' : 'Under',
            ];
        }

        return $reportData;
    }


    private function getMonthlyReportData($filterMonth)
    {
        $agents = \App\Models\User::where('role', 'agent')->get();

        $year = substr($filterMonth, 0, 4);
        $month = substr($filterMonth, 5, 2);

        $reportData = [];

        foreach ($agents as $agent) {
            $bets = \App\Models\Bet::where('agent_id', $agent->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();

            $grossSales = $bets->sum('amount');

            $winnings = $bets->where('is_winner', 1)->sum(function ($bet) {
                return $bet->amount * (optional($bet->multiplier)->multiplier ?? 0);
            });

            $payouts = $winnings;

            $deductions = DB::table('deductions')
                ->where('agent_id', $agent->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('amount') ?? 0;

            $commission = $grossSales * 0.10;

            $payouts = $bets->where('is_winner', 1)->sum(function ($bet) {
                return $bet->amount * $bet->multiplier; // Already includes incentives
            });

            // Net Sales logic
            if ($payouts > 0) {
                $netSales = $grossSales - $commission - $payouts + $deductions;

            } else {
                $netSales = $grossSales - $commission + $deductions;
            }

            $netRemittance = \App\Models\Collection::where('agent_id', $agent->id)
                ->where('status', 'approved')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('net_remit');

            $difference = $netRemittance - $netSales;

            $reportData[] = [
                'agent' => $agent,
                'gross_sales' => $grossSales,
                'net_remittance' => $netRemittance,
                'net_sales' => $netSales,
                'difference' => $difference,
                'status' => abs($difference) < 0.01 ? 'Balanced' : 'Under',
            ];
        }

        return $reportData;
    }

    private function getYearlyReportData($filterYear)
    {
        $agents = \App\Models\User::where('role', 'agent')->get();

        $reportData = [];

        foreach ($agents as $agent) {
            $bets = \App\Models\Bet::where('agent_id', $agent->id)
                ->whereYear('created_at', $filterYear)
                ->get();

            $grossSales = $bets->sum('amount');

            $winnings = $bets->where('is_winner', 1)->sum(function ($bet) {
                return $bet->amount * (optional($bet->multiplier)->multiplier ?? 0);
            });

            $payouts = $winnings;

            $deductions = DB::table('deductions')
                ->where('agent_id', $agent->id)
                ->whereYear('created_at', $filterYear)
                ->sum('amount') ?? 0;

            $commission = $grossSales * 0.10;

            $payouts = $bets->where('is_winner', 1)->sum(function ($bet) {
                return $bet->amount * $bet->multiplier; // Already includes incentives
            });

            // Net Sales logic
            if ($payouts > 0) {
                $netSales = $grossSales - $commission - $payouts + $deductions;

            } else {
                $netSales = $grossSales - $commission + $deductions;
            }

            $netRemittance = \App\Models\Collection::where('agent_id', $agent->id)
                ->where('status', 'approved')
                ->whereYear('created_at', $filterYear)
                ->sum('net_remit');

            $difference = $netRemittance - $netSales;

            $reportData[] = [
                'agent' => $agent,
                'gross_sales' => $grossSales,
                'net_remittance' => $netRemittance,
                'net_sales' => $netSales,
                'difference' => $difference,
                'status' => abs($difference) < 0.01 ? 'Balanced' : 'Under',
            ];
        }

        return $reportData;
    }
    //-----------end of admin reports----------->
    // ---print report-->
    public function printReport(Request $request)
    {
        $filterType = $request->input('type'); // daily, weekly, monthly, yearly
        $filterValue = $request->input('value');

        switch ($filterType) {
            case 'daily':
                $data = $this->getDailyReportData($filterValue);
                $formattedTitle = "Daily Report for " . Carbon::parse($filterValue)->toFormattedDateString();
                break;

            case 'weekly':
                $data = $this->getWeeklyReportData($filterValue);
                $startOfWeek = Carbon::parse($filterValue)->startOfWeek();
                $endOfWeek = Carbon::parse($filterValue)->endOfWeek();
                $formattedTitle = "Weekly Report ({$startOfWeek->toFormattedDateString()} - {$endOfWeek->toFormattedDateString()})";
                break;

            case 'monthly':
                $data = $this->getMonthlyReportData($filterValue);
                $formattedTitle = "Monthly Report for " . Carbon::parse($filterValue)->format('F Y');
                break;

            case 'yearly':
                $data = $this->getYearlyReportData($filterValue);
                $formattedTitle = "Yearly Report for " . $filterValue;
                break;

            default:
                abort(400, 'Invalid filter type.');
        }
            Log::info("Printing Report", [
                'type' => $filterType,
                'value' => $filterValue
            ]);

        return view('admin.reports.printreport', [
            'data' => $data,
            'formattedTitle' => $formattedTitle,
            'type' => $filterType,
            'filterValue' => $filterValue,
        ]);
    }

    public function printStubs(Request $request)
    {
        $selectedAgentId = $request->input('agent_id');
        $date = $request->input('date'); // optional, like '2025-07-30'

        $query = Collection::with('agent.user')
            ->select('id', 'stub_id', 'agent_id', 'name')
            ->selectRaw('COALESCE(SUM(net_remit), 0) as total_amount')
            ->selectRaw('MAX(game_date) as latest_game_date')
            ->groupBy('stub_id', 'agent_id', 'name');

        if ($selectedAgentId) {
            $query->where('agent_id', $selectedAgentId);
        }

        if ($date) {
            $query->whereDate('collection_date', $date);
        }

        $stubs = $query->get();

        // Compute overall total
        $totalAmount = $stubs->sum(function ($stub) {
            return $stub->total_amount ?? 0;
        });

        // Get agent name
        $agentName = null;
        if ($selectedAgentId) {
            $user = User::find($selectedAgentId);
            $agentName = $user?->name ?? 'N/A';
        }

        return view('reports.print-stubs', compact(
            'stubs',
            'selectedAgentId',
            'totalAmount',
            'agentName'
        ));
    }



    // current time          
}



