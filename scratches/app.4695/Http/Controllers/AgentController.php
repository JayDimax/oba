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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
            ->whereDate('created_at', now())
            ->get();

        $todayGross = $todayBets->sum('amount');
        $totalWins = $todayBets->where('is_win', true)->sum('payout');
        $totalBets = $todayBets->count();

        return view('agent.dashboard', compact('todayBets', 'todayGross', 'totalWins', 'totalBets'));
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

        // Preload multipliers and map to game_type => value
        $defaultMultipliers = Multiplier::pluck('multiplier', 'game_type')->toArray();

        $bets = Bet::where('agent_id', $agentId)
            ->whereDate('game_date', $date)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('stub_id')
            ->map(function ($group) use ($defaultMultipliers) {
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

        return view('agent.bet-history', [
            'bets'       => $bets,
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


// review this tomorrow asap---->

    public function reports(Request $request) 
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

        return view('agent.reports', [
            'date'    => $date,
            'draw'    => $draw,
            'summary' => $summary,
            'reports' => $bets,
            'perDrawStats' => $perDrawStats,
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
    public function collections(Request $request)
    {
        $agent = auth()->user();
        
        $approvedCollections = \App\Models\Collection::where('agent_id', $agent->id)
        ->where('status', 'approved')
        ->where('is_remitted', true)
        ->whereNotNull('verified_by')
        ->whereNotNull('verified_at')
        ->orderByDesc('collection_date')
        ->get();
            

        $cashier = $agent->cashier;
        $date = $request->input('date', now()->toDateString());





        $collections = \App\Models\Collection::where('agent_id', $agent->id)
        ->whereIn('status', ['pending', 'approved']) // Or only approved if finalized
        ->orderByDesc('collection_date')
        ->get(['collection_date', 'gross', 'net_remit', 'status']);
        $totalRemittance = $collections->sum('net_remit');

        // 1. Get stub IDs already submitted (all statuses)
        $submittedStubIds = DB::table('collection_stub')
            ->join('collections', 'collection_stub.collection_id', '=', 'collections.id')
            ->where('collections.agent_id', $agent->id)
            ->whereDate('collections.collection_date', $date)
            ->pluck('collection_stub.stub_id')
            ->toArray();

        // 2. Group bets for this agent that have not yet been submitted
        $groupedUnsubmitted = DB::table('bets')
            ->select('stub_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as total_bets'))
            ->where('agent_id', $agent->id)
            ->whereDate('created_at', $date)
            ->whereNotIn('stub_id', $submittedStubIds)
            ->groupBy('stub_id')
            ->get();

        $totalUnremittedGross = DB::table('bets')
        ->where('agent_id', $agent->id)
        ->whereDate('created_at', $date)
        ->whereNotIn('stub_id', $submittedStubIds)
        ->sum('amount');

        $unremittedBets = DB::table('bets')
            ->where('agent_id', $agent->id)
            ->whereDate('created_at', $date)
            ->whereNotIn('stub_id', $submittedStubIds)
            ->get();

        $netRemitFromUnremitted = $unremittedBets->sum(function ($bet) {
            // Only subtract commission base; winnings include bonus
            $commissionPercent = DB::table('agent_commissions')
                ->where('agent_id', $bet->agent_id)
                ->where('game_type', $bet->game_type)
                ->value('commission_percent') ?? 10;

            $commissionBase = $bet->amount * ($commissionPercent / 100);

            return $bet->is_winner
                ? $bet->amount - $commissionBase - ($bet->winnings ?? 0)
                : $bet->amount - $commissionBase;
        });






        $unsubmittedStubIds = $groupedUnsubmitted->pluck('stub_id');

        $unsubmittedBets = DB::table('bets')
            ->whereIn('stub_id', $unsubmittedStubIds)
            ->get();

        $previewNetRemit = $unsubmittedBets->sum(function ($bet) {
            return $bet->is_winner ? $bet->amount : $bet->amount * 0.9;
        });

        $projectedCommission = $unsubmittedBets->where('is_winner', false)->sum('amount') * 0.10;
        $totalWinnings = $unsubmittedBets->where('is_winner', true)->sum('amount');
        $totalNonWinnings = $unsubmittedBets->where('is_winner', false)->sum('amount');

        $gross = $totalWinnings + $totalNonWinnings;
        $commission = $gross * 0.10;
        $incentives = $totalWinnings * (1 / 3);
        $projectedIncome = $commission + $incentives;


        // 3. Get all collections with approved status for that date 
        $approvedCollections = \App\Models\Collection::with('collectionStubs.bets')
            ->where('agent_id', $agent->id)
            ->whereDate('collection_date', $date)
            ->where('status', 'approved')
            ->get();

        // 4. Compute Net Remitted Amount (with 10% deduction on non-winning bets)
        $netRemittedAmount = 0;

        foreach ($approvedCollections as $collection) {
            foreach ($collection->collectionStubs as $stub) {
                foreach ($stub->bets as $bet) {
                    // Deduct 10% if not winning
                    $netRemittedAmount += $bet->is_winner
                        ? $bet->amount
                        : $bet->amount * 0.9;
                }
            }
        }
        $agents = $cashier->assignedAgents()->get();
        $agentIds = $agents->pluck('id');
            // 7. Fetch pending collections for assigned agents
        $pendingCollections = Collection::with([
            'agent',
            'collectionStubs.bets'
        ])
        ->whereIn('agent_id', $agentIds)
        ->where('status', 'pending')
        ->orderByDesc('collection_date')
        ->get();



    
       $pendingAgents = $pendingCollections
        ->groupBy('agent_id')
        ->map(function ($collections, $agentId) {
            $agent = $collections->first()->agent;
            $totalRemittance = 0;

            foreach ($collections as $collection) {
                foreach ($collection->collectionStubs as $stub) {
                    $bets = $stub->bets ?? collect();
                    $gross = $bets->sum('amount');
                    $commission = $gross * 0.10;
                    $hasWinningBets = $bets->where('is_winner', true)->isNotEmpty();

                    $payouts = $bets->where('is_winner', true)->sum('payout') ?? 0;
                    $incentives = $bets->where('is_winner', true)->sum(function ($bet) {
                        return $bet->amount / 3;
                    });

                    // ✅ Get deduction based on agent + collection date
                    $deduction = Deduction::where('agent_id', $collection->agent_id)
                    ->whereDate('deduction_date', $collection->collection_date)
                    ->sum('amount');


                    if ($hasWinningBets) {
                        $netRemit = $gross - $commission - $incentives - $payouts + $deduction;
                    } else {
                        $netRemit = $gross - $commission + $deduction;
                    }

                    $totalRemittance += $netRemit;
                }
            }

            $agent->unpaid_amount = max($totalRemittance, 0);
            return $agent;
        });


        return view('agent.collections', [
            'agent' => $agent,
            'cashier' => $cashier,
            'date' => $date,
            'groupedStubs' => $groupedUnsubmitted,
            'netRemit' => $netRemittedAmount,
            'previewNetRemit' => $previewNetRemit,
            'projectedCommission' => $projectedCommission,
            'gross' => $gross,
            'commission' => $commission,
            'incentives' => $incentives,
            'projectedIncome' => $projectedIncome,
            'collections' => $collections,
            'totalRemittance' => $totalRemittance,
            'pendingAgents' => $pendingAgents, // ✅ fix key
            'approvedCollections' => $approvedCollections,
            'totalUnremittedGross' => $totalUnremittedGross,
            'netRemitFromUnremitted' => $netRemitFromUnremitted,

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

    public function collect(Request $request)
    {
        $agent = auth()->user();
        $agentId = $agent->id;
        $date = Carbon::parse($request->input('collection_date'));

        // 🕘 ENFORCE CUTOFF: Only allow remittance if current time is after 9 PM of the selected date
        $now = Carbon::now();
        $cutoffTime = Carbon::create($date->year, $date->month, $date->day, 21, 0, 0);

        if ($now->lt($cutoffTime)) {
            return redirect()->back()->with('error', 'Remittance not allowed yet. Please wait until after 9:00 PM of the selected date.');
        }

        // 🚫 Check for existing remittance
        $existing = Collection::where('agent_id', $agentId)
            ->whereDate('collection_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            return redirect()->back()->with('error', 'Remittance for this date has already been submitted.');
        }

        // ✅ Validate request
        $request->validate([
            'collection_date' => 'required|date',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // 📦 Fetch all bets for the specified date
        $bets = DB::table('bets')
            ->where('agent_id', $agentId)
            ->whereDate('game_date', $date)
            ->get();

        if ($bets->isEmpty()) {
            return back()->with('error', 'No bets found for the selected date.');
        }

        // 🧮 Prepare calculations
        $stubIds = $bets->pluck('stub_id')->unique()->values()->all();
        $gross = $bets->sum('amount');
        $commission = $gross * 0.10;

        $hasWinners = $bets->contains('is_winner', true);
        $payouts = $hasWinners ? $bets->where('is_winner', true)->sum('winnings') : 0;
        $incentives = $hasWinners ? $bets->where('is_winner', true)->sum('amount') * (1 / 3) : 0;

        $netRemit = $hasWinners
            ? $gross - $commission - $incentives - $payouts
            : $gross - $commission;

        // 📁 Handle proof file upload
        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('proofs', 'public');
        }

        // 💾 Save collection record
        $collection = Collection::create([
            'agent_id' => $agentId,
            'collection_date' => $date,
            'gross' => $gross,
            'payouts' => $payouts,
            'commission' => $commission,
            'incentives' => $incentives,
            'projected_income' => $commission + $incentives,
            'net_remit' => max($netRemit, 0),
            'proof_file' => $proofPath,
            'is_remitted' => true,
            'status' => 'pending',
        ]);

        // 🔗 Link stub IDs
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
    public function remitPreview(Request $request)
    {
        $date = Carbon::parse($request->input('date'));
        $agentId = auth()->id();

        // Check if already remitted
        $alreadyRemitted = \App\Models\Collection::where('agent_id', $agentId)
            ->whereDate('collection_date', $date)
            ->where('is_remitted', true)
            ->exists();

        if ($alreadyRemitted) {
            return response()->json([
                'status' => 'remitted',
                'message' => 'Already remitted on this date.'
            ]);
        }

        // Get bets for the day
        $bets = DB::table('bets')
            ->where('agent_id', $agentId)
            ->whereDate('game_date', $date)
            ->get();

        if ($bets->isEmpty()) {
            return response()->json([
                'status' => 'empty',
                'message' => 'No bets found for this date.'
            ]);
        }

        $gross = $bets->sum('amount');
        $commission = $gross * 0.10;

        $hasWinners = $bets->contains('is_winner', true);
        $payouts = $hasWinners ? $bets->where('is_winner', true)->sum('winnings') : 0;
        $incentives = $hasWinners ? $bets->where('is_winner', true)->sum('amount') * (1 / 3) : 0;

        $netRemit = $hasWinners
            ? $gross - $commission - $incentives - $payouts
            : $gross - $commission;

        return response()->json([
            'status' => 'ok',
            'gross' => $gross,
            'commission' => $commission,
            'payouts' => $payouts,
            'incentives' => $incentives,
            'net_remit' => max($netRemit, 0),
        ]);
    }

    private function computeNetRemittance($agentId, $date)
    {
        $bets = \App\Models\Bet::where('agent_id', $agentId)
                    ->whereDate('game_date', $date)
                    ->get();

        $gross = $bets->sum('amount');
        $commissionBase = $gross * 0.10;

        // Commission Bonus from actual column, filtered by is_winner
        $commissionBonus = $bets->where('is_winner', true)
                                ->sum('commission_bonus');

        $deduction = \App\Models\Deduction::where('agent_id', $agentId)
                    ->whereDate('deduction_date', $date)
                    ->value('amount') ?? 0;

        $netSales = $gross - $commissionBase;

        return $netSales - $commissionBonus + $deduction;
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

        return view('agent.dashboard', compact('agent', 'isActive'));
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
        $ids = explode(',', $stub_ids);
        $bets = Bet::whereIn('stub_id', $ids)->with('agent')->get();

        return view('agent.prints.multi', compact('bets'));
    }

}
