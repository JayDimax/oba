<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bet;
use App\Models\User;
use App\Models\Agent;
use App\Models\Result;
use App\Models\Deduction; 
use App\Models\Collection;
use App\Models\AgentBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\RemittanceCalculator;

class DashboardController extends Controller
{
    // admin dashboard here ---->>>

/**
 * Show the Admin dashboard (summary + filters)
 *
 * Expected Remittance = Gross − Winnings − Base Commission (10 %) + Deductions
 * Deficit            = max(0, Winnings + Commission − (Gross + Deductions))
 *
 * All figures are for “today” unless the user applies From / To / Draw / Agent filters.
 */
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
        // Clone query to avoid interfering with pagination or .get()------------->>>>>>>>>>
       $filteredBets = (clone $betsQuery)->get();
        $filteredGross = $filteredBets->sum('amount');
        $filteredCommission = $filteredBets->sum(function ($bet) {
            return $bet->is_winner ? 0 : $bet->amount * 0.10;
        });
        $filteredWinnings = $filteredBets->sum(function ($bet) {
            return $bet->is_winner ? $bet->prize_amount ?? 0 : 0;
        });
        $filteredNetSales = $filteredBets->sum(function ($bet) {
            if ($bet->is_winner) {
                return $bet->amount - $bet->prize_amount + 0; // commission excluded
            } else {
                return $bet->amount - ($bet->amount * 0.10);
            }
        });
        $filteredGross      = $filteredTotals->gross ?? 0;
        $filteredCommission = $filteredTotals->commission ?? 0;
        $filteredNetSales   = $filteredTotals->net_sales ?? 0;
        //------------------------------------------------>>>>>>>

        $bets = $betsQuery->orderByDesc('created_at')->get();

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
            ->map(fn($g) => $g->take(3));

        $allResults = Result::whereDate('created_at', today())->get();

        $agents = Agent::orderBy('name')->get();
        /** ─────────────────────────────────────────────
         *  5. PASS TO VIEW
         *  ────────────────────────────────────────────*/
        return view('admin.dashboard', compact(
            // headline
            'todaySummary',
            'totalDeductions',
            'actualRemittance',
            'unremittedBalance',
            'deficit',
            'netSales',
            'allResults',


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
        // 1. Handle Tab & Base Report Data (Daily, Weekly, Monthly, Yearly)
        // ─────────────────────────────────────────────
        $tab = $request->input('tab', 'daily');

        switch ($tab) {
            case 'monthly':
                $filterMonth = $request->input('filter_month', now()->format('Y-m'));
                $reportData = $this->getMonthlyReportData($filterMonth);
                return view('admin.reports.index', compact('tab', 'reportData', 'filterMonth'));

            case 'yearly':
                $filterYear = $request->input('filter_year', now()->format('Y'));
                $reportData = $this->getYearlyReportData($filterYear);
                return view('admin.reports.index', compact('tab', 'reportData', 'filterYear'));

            case 'weekly':
                $filterWeek = $request->input('filter_week', now()->startOfWeek()->format('Y-m-d'));
                $reportData = $this->getWeeklyReportData($filterWeek);
                return view('admin.reports.index', compact('tab', 'reportData', 'filterWeek'));

            case 'daily':
            default:
                $filterDate = $request->input('filter_date', now()->toDateString());
                $reportData = $this->getDailyReportData($filterDate);
                return view('admin.reports.index', compact('tab', 'reportData', 'filterDate'));
        }

        $today = now();

        // ───── Filter Inputs ─────
        $from = $request->input('from_date', $today->toDateString());
        $to = $request->input('to_date', $today->toDateString());
        $drawFilter = $request->input('draw_time', 'ALL');
        $agentName = $request->input('agent_name');

        // ───── Map Draw Time to Value ─────
        $drawMap = ['2PM' => '14', '5PM' => '17', '9PM' => '21'];
        $drawTimeValue = ($drawFilter !== 'ALL' && isset($drawMap[$drawFilter]))
            ? $drawMap[$drawFilter]
            : null;

        // ───── Main Query ─────
        $betsQuery = Bet::with(['agent.user'])
            ->whereBetween('game_date', [$from, $to])
            ->when($drawTimeValue, fn($q) => $q->where('game_draw', $drawTimeValue))
            ->when($agentName, fn($q) =>
                $q->whereHas('agent.user', fn($q2) =>
                    $q2->where('name', 'like', "%{$agentName}%")
                )
            );

        $filteredBets = $betsQuery->get();

        // ───── Totals Computation ─────
        $gross = $filteredBets->sum('amount');

        $commission = $filteredBets->sum(function ($bet) {
            return $bet->is_winner ? 0 : $bet->amount * 0.10;
        });

        $winnings = $filteredBets->sum(function ($bet) {
            return $bet->is_winner ? ($bet->prize_amount ?? 0) : 0;
        });

        $netSales = $filteredBets->sum(function ($bet) {
            if ($bet->is_winner) {
                return $bet->amount - ($bet->prize_amount ?? 0);
            } else {
                return $bet->amount - ($bet->amount * 0.10);
            }
        });
$agents = Agent::orderBy('name')->get();
        // ───── Final Data ─────
        $reportData = [
            'gross' => $gross,
            'commission' => $commission,
            'winnings' => $winnings,
            'net_sales' => $netSales,
            'filtered_bets' => $filteredBets,
        ];

        return view('admin.reports.index', compact(
            'reportData', 'from', 'to', 'drawFilter', 'agentName','agents'
        ));
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



    // current time          
}



