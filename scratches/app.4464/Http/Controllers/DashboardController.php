<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bet;
use App\Models\User;
use App\Models\Result;
use App\Models\Collection;
use App\Models\AgentBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // admin dashboard here ---->>>
// ================= Admin Controller Refactored =================

// ================= Admin Controller Refactored =================

public function admin(Request $request)
{
    $today = today();
    $now = now();

    // =========[ DYNAMIC COMMISSION CALCULATION ]=========
    $betsToday = Bet::with('agent')->whereDate('created_at', $today)->get();

    $totalGross = $betsToday->sum('amount');
    $totalCommission = 0;

    // Group bets by agent and game_type
    $grouped = $betsToday->groupBy(fn($bet) => $bet->agent_id . '-' . $bet->game_type);

    // Fetch commission rates for involved agent-game_type pairs
    $commissionRates = DB::table('agent_commissions')
        ->whereIn('agent_id', $betsToday->pluck('agent_id')->unique())
        ->whereIn('game_type', $betsToday->pluck('game_type')->unique())
        ->get()
        ->keyBy(fn($row) => $row->agent_id . '-' . $row->game_type);

    foreach ($grouped as $key => $groupBets) {
        [$agentId, $gameType] = explode('-', $key);
        $rate = $commissionRates[$key]->rate ?? 0;
        $gross = $groupBets->sum('amount');
        $totalCommission += $gross * $rate;
    }

    // =========[ FINANCIAL SUMMARY ]=========
    $totalWinningBetAmount = $betsToday->where('is_winner', 1)->sum('amount');

    $totalWinnings = Bet::join('multipliers', 'bets.game_type', '=', 'multipliers.game_type')
        ->whereDate('bets.created_at', $today)
        ->where('bets.is_winner', 1)
        ->select(DB::raw('SUM(bets.amount * multipliers.multiplier) as total'))
        ->value('total') ?? 0;

    $netAfterCommission = $totalGross - $totalCommission;
    $netSales = $netAfterCommission - $totalWinnings;
    $deficit = $netAfterCommission - $totalWinnings;
    $expectedRemittance = $netAfterCommission - $totalWinnings;

    $actualRemittance = Collection::whereDate('created_at', $today)
        ->where('status', 'approved')
        ->sum('net_remit');

    $unremittedBalance = $expectedRemittance - $actualRemittance;

    // =========[ AGENT STATS ]=========
    $totalAgents = User::where('role', 'agent')->count();
    $activeAgents = User::where('role', 'agent')->where('is_active', true)->count();
    $blockedAgents = $totalAgents - $activeAgents;
    $agentsWithBalance = AgentBalance::where('amount', '>', 0)->count();

    // =========[ NEXT DRAW TIME ]=========
    $nextDraw = collect([
        ['label' => '2PM', 'time' => $today->copy()->setTime(14, 0)],
        ['label' => '5PM', 'time' => $today->copy()->setTime(17, 0)],
        ['label' => '9PM', 'time' => $today->copy()->setTime(21, 0)],
    ])->first(fn($draw) => $draw['time']->greaterThan($now));

    $latestResult = Result::latest('game_date')->first();

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
        ->get();

    // =========[ TOP COMBINATIONS ]=========
    $topCombinations = DB::table('bets')
        ->select('game_type', 'bet_number', DB::raw('COUNT(*) as total'))
        ->whereDate('game_date', $today)
        ->groupBy('game_type', 'bet_number')
        ->orderByDesc('total')
        ->get()
        ->groupBy('game_type')
        ->map(fn($group) => $group->take(3));

    return view('admin.dashboard', compact(
        'totalGross',
        'totalCommission',
        'netAfterCommission',
        'totalWinnings',
        'expectedRemittance',
        'actualRemittance',
        'unremittedBalance',
        'deficit',
        'netSales',
        'totalAgents',
        'activeAgents',
        'blockedAgents',
        'agentsWithBalance',
        'nextDraw',
        'latestResult',
        'agents',
        'bets',
        'from',
        'to',
        'draw',
        'agentCodes',
        'agentName',
        'topCombinations'
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

            // Incentives: 3x per peso bet for winners
            $incentives = $bets->where('is_winner', 1)->sum(function ($bet) {
                return $bet->amount * 3;
            });

            // Deductions: fetched directly from deductions table
            $deductions = DB::table('deductions')
                ->where('agent_id', $agent->id)
                ->whereDate('created_at', $filterDate)
                ->sum('amount') ?? 0;

            // Commission: 10% default of gross
            $commission = $grossSales * 0.10;

            // Net Sales logic
            if ($winnings > 0) {
                $netSales = $grossSales - $commission - $incentives - $payouts + $deductions;
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
                'status' => $difference >= 0 ? 'Balanced' : 'Under',
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

            $incentives = $bets->where('is_winner', 1)->sum(function ($bet) {
                return $bet->amount * 3;
            });

            // Deductions from `deductions` table
            $deductions = DB::table('deductions')
                ->where('agent_id', $agent->id)
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount') ?? 0;

            // Commission
            $commission = $grossSales * 0.10;

            // Net Sales calculation
            if ($winnings > 0) {
                $netSales = $grossSales - $commission - $incentives - $payouts + $deductions;
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

            $incentives = $bets->where('is_winner', 1)->sum(function ($bet) {
                return $bet->amount * 3;
            });

            $deductions = DB::table('deductions')
                ->where('agent_id', $agent->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('amount') ?? 0;

            $commission = $grossSales * 0.10;

            if ($winnings > 0) {
                $netSales = $grossSales - $commission - $incentives - $payouts + $deductions;
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

            $incentives = $bets->where('is_winner', 1)->sum(function ($bet) {
                return $bet->amount * 3;
            });

            $deductions = DB::table('deductions')
                ->where('agent_id', $agent->id)
                ->whereYear('created_at', $filterYear)
                ->sum('amount') ?? 0;

            $commission = $grossSales * 0.10;

            if ($winnings > 0) {
                $netSales = $grossSales - $commission - $incentives - $payouts + $deductions;
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



