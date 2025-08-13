<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bet;
use App\Models\User;
use App\Models\Agent;
use App\Models\Result;
use App\Models\Deduction;
use App\Models\Collection;
use App\Models\Multiplier;
use Illuminate\Http\Request;
use App\Models\AgentCommission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\RemittanceCalculator;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Pagination\LengthAwarePaginator;

class AgentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $agent = auth()->user();

        $todayBets = $agent->bets()
            ->whereDate('game_date', now())
            ->get();

        $todayGross = $todayBets->sum('amount');
        $totalWins = $todayBets->where('is_win', true)->sum('payout');
        $totalBets = $todayBets->count();

        $today = Carbon::today();

        // --- SCENARIO 1: Top combinations for all bets (like the admin) ---
        $topCombinations = DB::table('bets')
        ->select('game_type', 'bet_number', DB::raw('COUNT(*) as total'))
        ->whereDate('game_date', $today)
        ->groupBy('game_type', 'bet_number')
        ->orderByDesc('total')
        ->get()
        ->groupBy('game_type')
        ->map(fn($g) => $g->take(3));

        return view('agent.dashboard', compact('todayBets', 'todayGross', 'totalWins', 'totalBets','topCombinations'));
    }
   
    public function showReceipt($stub){
        $bets = auth()->user()->bets()
            ->where('stub_id', $stub)
            ->get();

        if ($bets->isEmpty()) {
            abort(404, 'Receipt not found.');
        }

        $totalAmount = $bets->sum('amount');
        return view('agent.receipt', compact('bets', 'totalAmount', 'stub'));
    }

    public function print($stub)
    {
        $loggedInAgent = auth()->user(); // renamed for clarity

        // Get bets for this stub that belong to the current agent
        $bets = Bet::where('stub_id', $stub)
        ->where('agent_id', auth()->id())
        ->with('betAgent') // eager-load the user
        ->get();

        if ($bets->isEmpty()) {
            abort(404, 'No matching stub found for you.');
        }

        // Use relationship to get the name
        $agentName = optional($bets->first()->betAgent)->name ?? 'Unknown Agent';

        $drawDate = $bets->first()->created_at->format('Y-m-d');
        $printedTime = now()->format('Y-m-d h:i:s A');
        $totalAmount = $bets->sum('amount');

        $qrCodeImage = base64_encode(
            QrCode::format('png')->size(120)->generate("Stub-{$stub}")
        );

        return view('agent.receipt', compact('stub','bets','agentName','drawDate','printedTime','totalAmount','qrCodeImage'));
    }

    public function betHistory(Request $request)
    {
        $agentId = auth()->id();
        $date = $request->input('date', now()->toDateString());

        // Load multipliers
        $defaultMultipliers = Multiplier::pluck('multiplier', 'game_type')->toArray();

        // STEP 1: Get unique stub_ids for pagination
        $perPage = 5;
        $stubIds = Bet::where('agent_id', $agentId)
        ->whereDate('game_date', $date)
        ->groupBy('stub_id')
        ->orderByDesc(DB::raw('MAX(created_at)')) // sort by latest stub's created_at
        ->pluck('stub_id');


        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedStubIds = $stubIds->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // STEP 2: Fetch related bets for paginated stub_ids
        $betsRaw = Bet::where('agent_id', $agentId)
            ->whereDate('game_date', $date)
            ->whereIn('stub_id', $paginatedStubIds)
            ->orderByDesc('created_at')
            ->get();

        // STEP 3: Group and transform
        $bets = $betsRaw->groupBy('stub_id')->map(function ($group) use ($defaultMultipliers) {
            $gameType = $group->first()->game_type;

            return (object)[
                'stub_id'    => $group->first()->stub_id,
                'game_type'  => $gameType,
                'game_draw'  => $group->first()->game_draw,
                'game_date'  => $group->first()->game_date,
                'created_at' => $group->first()->created_at,
                'bets'       => $group->map(function ($item) use ($defaultMultipliers) {
                    $gameType = $item->game_type;
                    $multiplier = $item->multiplier ?? $defaultMultipliers[$gameType] ?? 1;

                    return (object)[
                        'bet_number' => $item->bet_number,
                        'amount'     => $item->amount,
                        'multiplier' => $multiplier,
                        'is_winner'  => $item->is_winner,
                    ];
                }),
                'total' => $group->sum(function ($b) use ($defaultMultipliers) {
                    $multiplier = $b->multiplier ?? $defaultMultipliers[$b->game_type] ?? 1;
                    return $b->amount * $multiplier;
                }),
                'winnings' => $group->where('is_winner', true)->sum(function ($b) use ($defaultMultipliers) {
                    $multiplier = $b->multiplier ?? $defaultMultipliers[$b->game_type] ?? 1;
                    return $b->amount * $multiplier;
                }),
            ];
        });

        // STEP 4: Wrap with paginator manually
        $paginated = new LengthAwarePaginator(
            $bets->values(),
            $stubIds->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('agent.bet-history', [
            'bets'       => $paginated,
            'date'       => Carbon::parse($date),
            'maxPastDate'=> Carbon::today()->subDays(2),
            'today'      => Carbon::today(),
        ]);
    }


    public function winningBets(Request $request)
    {
        $agentId = auth()->id();
        $drawDate = $request->input('draw_date', now()->toDateString());

        // Get all winning bets for this agent and date
        $bets = Bet::with(['claim', 'multiplier'])
            ->where('agent_id', $agentId)
            ->whereDate('game_date', $drawDate)
            ->where('is_winner', 1)
            ->get();

        // Separate bets based on existence of claim record
        $claimed = $bets->filter(fn($bet) => $bet->claim !== null);
        $unclaimed = $bets->filter(fn($bet) => $bet->claim === null);

        return view('agent.winning-bets', compact('drawDate', 'claimed', 'unclaimed'));
    }

    public function results(Request $request)
    {
        $agent = auth()->user();
        $date = $request->input('draw_date', now()->toDateString());
        $draw = $request->input('draw'); // '1st', '2nd', '3rd', or 'all'

        // Draw time map
        $drawMap = [
            '1st' => '14',
            '2nd' => '17',
            '3rd' => '21',
        ];

        // Base query: correct date
        $query = Result::whereDate('game_date', $date);

        // Filter by draw time if not "all"
        if ($draw !== 'all' && isset($drawMap[$draw])) {
            $query->where('game_draw', $drawMap[$draw]);
        }

        // Final results: should always include L2, S3, 4D
        $results = $query
            ->whereIn('game_type', ['L2', 'S3', '4D'])
            ->orderBy('game_draw')
            ->orderBy('game_type')
            ->get();

        return view('agent.results', compact('date', 'draw', 'results'));
    }

    public function reports(Request $request)
    {
        $agentId = auth()->id();
        $date = $request->input('draw_date', now()->toDateString());
        $draw = $request->input('draw'); // all, 1st, 2nd, 3rd

        // Map UI draw tabs to draw time codes
        $drawMap = [
            '1st' => '14',
            '2nd' => '17',
            '3rd' => '21',
        ];

        // Game types per draw time code
        $gameTypeMap = [
            '14' => 'L2',
            '17' => 'S3',
            '21' => '4D',
        ];

        // Base bet query filtered by agent and date
        $query = Bet::where('agent_id', $agentId)
                    ->whereDate('game_date', $date);

        // Apply draw filter if tab selected
        if (isset($drawMap[$draw])) {
            $query->where('game_draw', $drawMap[$draw]);
        }

        $bets = $query->get();

        // --- Universal commission calculation per game_type ---

        // Fetch all commission rates for this agent, keyed by uppercase game_type
        $commissionRates = AgentCommission::where('agent_id', $agentId)
            ->pluck('commission_percent', 'game_type')
            ->mapWithKeys(fn($rate, $gameType) => [strtoupper($gameType) => $rate])
            ->toArray();

        // Group bets by uppercase game_type
        $groupedBets = $bets->groupBy(fn($bet) => strtoupper($bet->game_type));

        $totalCommissionBase = 0;
        foreach ($groupedBets as $gameType => $groupedBet) {
            $grossByGameType = $groupedBet->sum('amount');
            $rate = $commissionRates[$gameType] ?? 0;
            $totalCommissionBase += $grossByGameType * ($rate / 100);
        }

        // Total gross across all bets
        $totalGross = $bets->sum('amount');
        $netSales = $totalGross - $totalCommissionBase;

        // Winning bets for hits, payouts, commission bonuses (filtered by draw tab)
        $winningBets = $bets->filter(function ($bet) use ($draw, $drawMap, $gameTypeMap) {
            if (!$bet->is_winner) return false;

            if ($draw === null || $draw === 'all') {
                return isset($gameTypeMap[$bet->game_draw]) && $bet->game_type === $gameTypeMap[$bet->game_draw];
            }

            $targetDrawCode = $drawMap[$draw] ?? null;
            $expectedGameType = $gameTypeMap[$targetDrawCode] ?? null;

            return $bet->game_draw === $targetDrawCode && $bet->game_type === $expectedGameType;
        });

        $commissionBonus = $winningBets->sum('commission_bonus');
        $hits = $winningBets->sum('amount');
        $payouts = $winningBets->sum('winnings');

        // Deductions from external table
        $deduction = Deduction::where('agent_id', $agentId)
            ->whereDate('deduction_date', $date)
            ->value('amount') ?? 0;

        // Final calculations
        $totalRemittance = $netSales + $deduction;
        $remittanceAfterPayouts = $totalRemittance - $payouts;

        $deficit = 0;
        if ($remittanceAfterPayouts < 0) {
            $deficit = abs($remittanceAfterPayouts);
        }

        // Stats per draw (14, 17, 21)
        $perDrawStats = [];
        foreach (['14', '17', '21'] as $drawCode) {
            $perDrawStats[$drawCode] = [
                'gross' => $bets->where('game_draw', $drawCode)->sum('amount'),
                'hits' => $bets->where('game_draw', $drawCode)->where('is_winner', true)->sum('amount'),
            ];
        }

        // Build summary array
        $summary = [
            'gross'            => $totalGross,
            'commission_base'  => $totalCommissionBase,
            'commission_bonus' => $commissionBonus,
            'commission'       => $totalCommissionBase + $commissionBonus,
            'net_sales'        => $netSales,
            'hits'             => $hits,
            'payouts'          => $payouts,
            'incentives'       => $commissionBonus,
            'deductions'       => $deduction,
            'net_total'        => $totalRemittance,
            'net_after_payouts'=> $remittanceAfterPayouts,
            'deficit'          => $deficit,
            'tapada'           => $deficit, // unclear but kept as in your code
        ];

        return view('agent.reports', [
            'date'        => $date,
            'draw'        => $draw,
            'summary'     => $summary,
            'reports'     => $bets,
            'perDrawStats'=> $perDrawStats,
        ]);
    }

    // summary receipt
    public function SummaryReceipt(Request $request)
    {
        
        $agentId = auth()->id();
        $date = $request->input('draw_date', now()->toDateString());
        $draw = $request->input('draw'); // all, 1st, 2nd, 3rd

        // Map: UI draw tabs to draw time codes
        $drawMap = [
            '1st' => '14',
            '2nd' => '17',
            '3rd' => '21',
        ];

        // Game types per draw time
        $gameTypeMap = [
            '14' => 'L2',
            '17' => 'S3',
            '21' => '4D',
        ];

        
        // Base bet query
        $query = Bet::where('agent_id', $agentId)
                    ->whereDate('game_date', $date);

        // Apply draw filter if tab is selected
        if (isset($drawMap[$draw])) {
            $query->where('game_draw', $drawMap[$draw]);
        }

        $bets = $query->get();   

        // Gross and commission
        $gross            = $bets->sum('amount');
        $commissionBase   = $gross * 0.10;
        $netSales         = $gross - $commissionBase;

        // Get winning bets based on current tab logic
        $winningBets = $bets->filter(function ($bet) use ($draw, $drawMap, $gameTypeMap) {
            if (!$bet->is_winner) return false;

            if ($draw === null || $draw === 'all') {
                // ALL tab: only include correct game type for each draw
                return isset($gameTypeMap[$bet->game_draw]) &&
                    $bet->game_type === $gameTypeMap[$bet->game_draw];
            }

            $targetDrawCode   = $drawMap[$draw] ?? null;
            $expectedGameType = $gameTypeMap[$targetDrawCode] ?? null;

            return $bet->game_draw === $targetDrawCode &&
                $bet->game_type === $expectedGameType;
        });

        // Draw-specific commission bonus (from DB)
        $commissionBonus = $winningBets->sum('commission_bonus');
        $hits            = $winningBets->sum('amount');
        $payouts         = $winningBets->sum('winnings');

        // Deduction (external table)
        $deduction = Deduction::where('agent_id', $agentId)
                            ->whereDate('deduction_date', $date)
                            ->value('amount') ?? 0;

        // Final computation
        $totalRemittance       = $netSales + $deduction;
        $remittanceAfterPayouts = $totalRemittance - $payouts;

        $perDrawStats = [];

        foreach (['14', '17', '21'] as $drawCode) {
            $perDrawStats[$drawCode] = [
                'gross' => $bets->where('game_draw', $drawCode)->sum('amount'),
                'hits' => $bets->where('game_draw', $drawCode)->where('is_winner', true)->sum('amount'),
            ];
        }


        // Build summary
        $summary = [
            'gross'              => $gross,
            'commission_base'    => $commissionBase,
            'commission_bonus'   => $commissionBonus,
            'commission'         => $commissionBase + $commissionBonus,
            'net_sales'          => $netSales,
            'hits'               => $hits,
            'payouts'            => $payouts,
            'incentives'         => $commissionBonus, // Synonym
            'deductions'         => $deduction,
            'net_total'          => $totalRemittance,
            'net_after_payouts'  => $remittanceAfterPayouts, 
        ];

        return view('agent.summary-receipt', [
            'date'    => $date,
            'draw'    => $draw,
            'summary' => $summary, 
            'reports' => $bets,
            'perDrawStats' => $perDrawStats,
        ]);

    }
    //colections view - agent side
    public function collections(Request $request)
    {
        $agent = auth()->user();
        $cashier = $agent->cashier;
        $date = $request->input('date', now()->toDateString());

        // Load agent
      

        // Fetch all stubs collected (remitted) by this agent on the selected date
        $collections = Collection::with('stubs')
            ->where('agent_id', $agent)
            ->whereDate('created_at', $date)
            ->get();

        // Flatten all stubs across all collections for this date
        $allStubIds = $collections->flatMap(function ($collection) {
            return $collection->stubs->pluck('id');
        });

        // Get all bets related to the remitted stubs
        $bets = Bet::whereIn('stub_id', $allStubIds)->get();

        // Calculate remittance using your existing calculator service
        $remit = \App\Services\RemittanceCalculator::computeNetRemit($bets);

        // Get approved collections
        $approvedCollections = \App\Models\Collection::with('collectionStubs.bets')
            ->where('agent_id', $agent->id)
            ->where('status', 'approved')
            ->where('is_remitted', true)
            ->whereNotNull('verified_by')
            ->whereNotNull('verified_at') 
            ->whereDate('collection_date', $date)
            ->orderByDesc('collection_date')
            ->get();
            
        $netRemittedAmount = 0;

            foreach ($approvedCollections as $collection) {
                foreach ($collection->collectionStubs as $stub) {
                    foreach ($stub->bets as $bet) {
                        $netRemittedAmount += RemittanceCalculator::computeNetRemit($bet);
                    }
                }
            }
        // Get collections (pending + approved)
        $collections = \App\Models\Collection::where('agent_id', $agent->id)
            ->whereIn('status', ['pending', 'approved'])
            ->orderByDesc('collection_date')
            ->get(['collection_date', 'gross', 'net_remit', 'status']);

        $totalRemittance = $collections->sum('net_remit');

        // Stub IDs already submitted
        $submittedStubIds = DB::table('collection_stub')
            ->join('collections', 'collection_stub.collection_id', '=', 'collections.id')
            ->where('collections.agent_id', $agent->id)
            ->whereDate('collections.collection_date', $date)
            ->pluck('collection_stub.stub_id')
            ->toArray();

        // Group unsubmitted stubs
        $groupedUnsubmitted = DB::table('bets')
            ->select('stub_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as total_bets'))
            ->where('agent_id', $agent->id)
            ->whereDate('created_at', $date)
            ->whereNotIn('stub_id', $submittedStubIds)
            ->groupBy('stub_id')
            ->get();

        // Get unremitted bets
        $unsubmittedBets = DB::table('bets')
            ->where('agent_id', $agent->id)
            ->whereDate('created_at', $date)
            ->whereNotIn('stub_id', $groupedUnsubmitted->pluck('stub_id'))
            ->get();

            $hasResults = DB::table('bets')
            ->where('agent_id', $agent->id)
            ->whereDate('game_date', $date)
            ->whereNotNull('is_winner')
            ->exists();

           
        $overallRemitBreakdown = null;

        if ($hasResults) {
            $betsForDate = Bet::where('agent_id', $agent->id)
                ->whereDate('game_date', $date)
                ->get();

            $overallRemitBreakdown = RemittanceCalculator::computeNetRemit($betsForDate);
            $overallRemittance = $overallRemitBreakdown['computed'];
        }

        // Use RemittanceCalculator for consistent logic
        $remit = RemittanceCalculator::computeNetRemit($unsubmittedBets);
        $gross = $remit['gross'];
        $commission = $remit['commission'];
        $incentives = $remit['incentives'];
        $previewNetRemit = $remit['computed'];
        $projectedIncome = $remit['projected_income'];
        $netRemitFromUnremitted = $remit['computed'];

        // Compute netRemittedAmount from approved collections
        $netRemittedAmount = 0;
        foreach ($approvedCollections as $collection) {
            $agent = $collection->agent;

            foreach ($collection->collectionStubs as $stub) {
                foreach ($stub->bets as $bet) {
                    // Get commission rate from agent_commissions table (fallback to 0)
                    $commissionRate = $agent->commissions()
                        ->where('game_type', $bet->game_type)
                        ->value('commission_percent') ?? 0;

                    if ($bet->is_winner) {
                        // Winner: agent remits full amount
                        $netRemittedAmount += $bet->amount;
                    } else {
                        // Loser: agent remits amount minus their commission
                        $commission = $bet->amount * ($commissionRate / 100);
                        $netRemittedAmount += ($bet->amount - $commission);
                    }
                }
            }
        }

        // Get pending collections of other agents for cashier display
        $agents = $cashier->assignedAgents()->get();
        $agentIds = $agents->pluck('id');

        $pendingCollections = Collection::with(['agent', 'collectionStubs.bets'])
            ->whereIn('agent_id', $agentIds)
            ->where('status', 'pending')
            ->orderByDesc('collection_date')
            ->get();

        $pendingAgents = $pendingCollections->groupBy('agent_id')->map(function ($collections, $agentId) {
            $agent = $collections->first()->agent;
            $totalRemittance = 0;

            foreach ($collections as $collection) {
                foreach ($collection->collectionStubs as $stub) {
                    $bets = $stub->bets ?? collect();
                    $gross = $bets->sum('amount');
                    $commission = $gross * 0.10;
                    $hasWinners = $bets->where('is_winner', true)->isNotEmpty();

                    $payouts = $bets->where('is_winner', true)->sum('payout') ?? 0;
                    $incentives = $bets->where('is_winner', true)->sum(fn($b) => $b->amount / 3);

                    $deduction = Deduction::where('agent_id', $collection->agent_id)
                        ->whereDate('deduction_date', $collection->collection_date)
                        ->sum('amount');

                    $netRemit = $hasWinners
                        ? $gross - $commission - $incentives - $payouts + $deduction
                        : $gross - $commission + $deduction;

                    $totalRemittance += $netRemit;
                }
            }

            $agent->unpaid_amount = max($totalRemittance, 0);
            return $agent;
        });

        // 🔄 Live daily cumulative preview for current date
        $today = now()->toDateString();
       $liveBets = Bet::where('agent_id', $agent->id)
            ->whereDate('created_at', $today)
            ->get();

        $liveRemit = RemittanceCalculator::computeNetRemit($liveBets);


        return view('agent.collections', [
            'agent' => $agent,
            'cashier' => $cashier,
            'date' => $date,
            'groupedStubs' => $groupedUnsubmitted,
            'netRemittedAmount' => $netRemittedAmount,
            'previewNetRemit' => $previewNetRemit,
            'projectedCommission' => $commission,
            'gross' => $gross,
            'commission' => $commission,
            'incentives' => $incentives,
            'projectedIncome' => $projectedIncome,
            'collections' => $collections,
            'totalRemittance' => $totalRemittance,
            'pendingAgents' => $pendingAgents,
            'approvedCollections' => $approvedCollections,
            'totalUnremittedGross' => $gross,
            'netRemitFromUnremitted' => $netRemitFromUnremitted,
            'liveNetRemitPreview' => $liveRemit['computed'],
            'liveNetRemitBreakdown' => $liveRemit,
            'hasResults' => $hasResults,
            'remittanceBreakdown' => $remit, 
            'overallRemittance' => $overallRemittance,
            'overallRemitBreakdown' => $overallRemitBreakdown,
        ]);
    }

 
    //submitting collections to cashier by agent
    public function collect(Request $request)
    {
        $agent = auth()->user();
        $agentId = $agent->id;
        $date = Carbon::parse($request->input('collection_date'));

        $now = Carbon::now();
        $cutoffTime = Carbon::create($date->year, $date->month, $date->day, 21, 0, 0);
        if ($now->lt($cutoffTime)) {
            return redirect()->back()->with('error', 'Remittance not allowed yet. Please wait until after 9:00 PM of the selected date.');
        }

        $existing = Collection::where('agent_id', $agentId)
            ->whereDate('collection_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            return redirect()->back()->with('error', 'Remittance for this date has already been submitted.');
        }

        $request->validate([
            'collection_date' => 'required|date',
            'manual_net_remit' => 'nullable|numeric|min:0',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $bets = DB::table('bets')
            ->where('agent_id', $agentId)
            ->whereDate('game_date', $date)
            ->get();

        if ($bets->isEmpty()) {
            return back()->with('error', 'No bets found for the selected date.');
        }

        $stubIds = $bets->pluck('stub_id')->unique()->values()->all();

        $remittance = RemittanceCalculator::computeNetRemit($bets, $request->input('manual_net_remit'));

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('proofs', 'public');
        }

        $collection = Collection::create([
            'agent_id' => $agentId,
            'collection_date' => $date,
            'gross' => $remittance['gross'],
            'payouts' => $remittance['payouts'],
            'commission' => $remittance['commission'],
            'incentives' => $remittance['incentives'],
            'projected_income' => $remittance['projected_income'],
            'net_remit' => $remittance['final'],
            'proof_file' => $proofPath,
            'is_remitted' => true,
            'status' => 'pending',
        ]);

        foreach ($stubIds as $stubId) {
            DB::table('collection_stub')->insert([
                'collection_id' => $collection->id,
                'stub_id' => $stubId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('agent.collections', ['date' => $date->toDateString()])
            ->with('success', 'Remittance submitted successfully.');
    }


    public function previewNetRemit(Request $request)
{
    $agent = auth()->user();
    $date = $request->input('date', now()->toDateString());

    $submittedStubIds = DB::table('collection_stub')
        ->join('collections', 'collection_stub.collection_id', '=', 'collections.id')
        ->where('collections.agent_id', $agent->id)
        ->whereDate('collections.collection_date', $date)
        ->pluck('collection_stub.stub_id')
        ->toArray();

    $unsubmittedBets = DB::table('bets')
        ->where('agent_id', $agent->id)
        ->whereDate('created_at', $date)
        ->whereNotIn('stub_id', $submittedStubIds)
        ->get();

    $remit = RemittanceCalculator::computeNetRemit($unsubmittedBets);

    return response()->json([
        'net_remit' => round($remit['computed'], 2),
    ]);
}

    public function showRemittancePreview()
    {
        $agentId = auth()->id();
        $today = now()->toDateString(); // or pass a specific date

        // Load all bets for current agent and date
        $bets = Bet::where('agent_id', $agentId)
            ->whereDate('game_date', $today)
            ->get();

        $gross = $bets->sum('amount');

        // Compute net remitted using 10% rule
        $netRemittedAmount = $bets->sum(function ($bet) {
            return $bet->is_winner ? $bet->amount : $bet->amount * 0.9;
        });

        return view('agent.collections', compact('gross', 'netRemittedAmount', 'today',));
    }

    public function remitPreview(Request $request)
    {
        $agentId = auth()->id();
        $date = $request->input('date');

        // ✅ Check for existing submission
        $existing = Collection::where('agent_id', $agentId)
            ->whereDate('collection_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            return response()->json([
                'status' => 'remitted',
            ]);
        } 

        // ✅ Get bets only with results
        $bets = DB::table('bets')
            ->where('agent_id', $agentId)
            ->whereDate('game_date', $date)
            ->whereNotNull('is_winner')
            ->get();

        if ($bets->isEmpty()) {
            return response()->json([
                'status' => 'empty',
            ]);
        }

        $remit = \App\Services\RemittanceCalculator::computeNetRemit($bets);

        return response()->json([
            'status' => 'ok',
            'gross' => $remit['gross'],
            'commission' => $remit['commission'],
            'incentives' => $remit['incentives'],
            'payouts' => $remit['payouts'],
            'net_remit' => $remit['computed'],
        ]);
    }

    public function support() {
        return view('agent.support');
    }

    public function settings() {
        return view('agent.settings');
    }
    public function bet()
    {
        $agent = auth()->user();

        $todayBets = $agent->bets()
            ->whereDate('created_at', now())
            ->get();

        $todayGross = $todayBets->sum('amount');
        $totalWins = $todayBets->where('is_win', true)->sum('payout');
        $totalBets = $todayBets->count();

        return view('agent.bet', compact('todayBets', 'todayGross', 'totalWins', 'totalBets'));
        
    }

    public function dashboard()
{
    $user = auth()->user();
    $agent = Agent::where('user_id', $user->id)->first();
    $isActive = $user->is_active;

    // Define today's date
    $today = Carbon::today();

    // --- SCENARIO 1: Top combinations for all bets (like the admin) ---
    $topCombinations = DB::table('bets')
        ->select('game_type', 'bet_number', DB::raw('COUNT(*) as total'))
        ->whereDate('game_date', $today)
        ->groupBy('game_type', 'bet_number')
        ->orderByDesc('total')
        ->get()
        ->groupBy('game_type')
        ->map(fn($g) => $g->take(5));

    // --- SCENARIO 2: Top combinations only for this agent's bets ---
    // If you have an `agent_id` column on your `bets` table, use this:
    // $topCombinations = DB::table('bets')
    //     ->select('game_type', 'bet_number', DB::raw('COUNT(*) as total'))
    //     ->where('agent_id', $agent->id) // Assuming $agent is available and has an ID
    //     ->whereDate('game_date', $today)
    //     ->groupBy('game_type', 'bet_number')
    //     ->orderByDesc('total')
    //     ->get()
    //     ->groupBy('game_type')
    //     ->map(fn($g) => $g->take(5));

    return view('agent.dashboard', compact('agent', 'isActive', 'topCombinations'));
}

    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|max:2048', 
        ]);
        $user = auth()->user();
        $agent = Agent::where('user_id', $user->id)->first();
        if (!$agent) {
            return back()->with('error', 'Agent record not found.');
        }
        if ($request->hasFile('profile_picture')) {
            if ($agent->profile_picture) {
                Storage::disk('public')->delete($agent->profile_picture);
            }
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $agent->profile_picture = $path;
            $agent->save();
        }

        return back()->with('success', 'Profile picture updated.');
    }

    public function showSummaryReceipt($stub)
    {
        $bets = Bet::where('stub_id', $stub)
            ->where('agent_id', auth()->id())
            ->get();

        if ($bets->isEmpty()) {
            abort(404, 'Receipt not found.');
        }

        // Calculate summary
        $summary = [
            'gross' => $bets->sum('amount'),
            'hits' => $bets->where('is_winner', 1)->sum('amount'),
            'net_sales' => $bets->sum('amount') - $bets->where('is_winner', 1)->sum('amount'),
            'payouts' => $bets->where('is_winner', 1)->sum('winnings'),
            'commission_base' => $bets->sum('commission_base'),
            'incentives' => $bets->sum('commission_bonus'),
            'deductions' => $bets->sum('commission_base') + $bets->sum('commission_bonus'),
            'net_after_payouts' => $bets->sum('amount') - $bets->where('is_winner', 1)->sum('winnings'),
        ];

        $draw = 'all'; // or extract from bet if needed

        return view('agent.prints.summary-receipt', compact('summary', 'draw'));
    }

    public function multi($stub_ids)
    {
        // Split the comma-separated stub IDs
        $ids = explode(',', $stub_ids);

        // Fetch bets for those stubs, including agent relationship
        $bets = Bet::whereIn('stub_id', $ids)->with('betAgent')->get();

        // Group bets by stub
        $receipts = $bets->groupBy('stub_id')->map(function ($group, $stub) {
            $firstBet = $group->first();

            return (object)[
                'stub' => $stub,
                'agentName' => optional($firstBet->betAgent)->name ?? 'Unknown Agent',
                'totalAmount' => $group->sum('amount'),
                'bets' => $group->map(function ($bet) {
                    return [
                        'draw' => $bet->draw,
                        'game' => $bet->game,
                        'combi' => $bet->combi,
                        'amount' => $bet->amount,
                    ];
                })->values()
            ];
        })->values(); // re-index array for Blade

        // Pass $receipts to Blade
        return view('agent.prints.multi', compact('receipts'));
    }


}
