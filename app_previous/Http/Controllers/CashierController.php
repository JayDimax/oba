<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bet;
use App\Models\User;
use App\Models\Agent;
use App\Models\Receipt;
use App\Models\Deduction;
use App\Models\Collection;
use App\Models\AgentBalance;
use Illuminate\Http\Request;
use App\Models\RemittanceBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\ReportService;

class CashierController extends Controller
{
    public function dashboard(Request $request)
    {
        $cashier = auth()->user();
        $gameDate = $request->input('game_date', now()->toDateString());

        // Assigned agents of this cashier
        $agents = $cashier->assignedAgents()->get();

        $agentRemittances = [];
        foreach ($agents as $agent) {
            $date = $request->input('date', now()->toDateString());

            $hasResults = DB::table('bets')
                ->where('agent_id', $agent->id)
                ->whereDate('game_date', $date)
                ->whereNotNull('is_winner')
                ->exists();

            if ($hasResults) {
                $bets = DB::table('bets')
                    ->where('agent_id', $agent->id)
                    ->whereDate('game_date', $date)
                    ->get();

                $remit = \App\Services\RemittanceCalculator::computeNetRemit($bets);

                $agentRemittances[$agent->id] = [
                    'name' => $agent->name,
                    'amount' => $remit['computed'],
                    'has_results' => true,
                ];
            } else {
                $agentRemittances[$agent->id] = [
                    'name' => $agent->name,
                    'amount' => null,
                    'has_results' => false,
                ];
            }
        }

        $agentIds = $agents->pluck('id')->toArray();

        // Load commissions for agents
        $agentCommissions = DB::table('agent_commissions')
            ->whereIn('agent_id', $agentIds)
            ->get()
            ->groupBy('agent_id');

        // All bets on the gameDate for assigned agents
        $bets = DB::table('bets')
            ->whereIn('agent_id', $agentIds)
            ->whereDate('game_date', $gameDate)
            ->get();
        $betsByAgent = $bets->groupBy('agent_id');

        // Gross sales per agent
        $grossSales = $betsByAgent->map(fn($bets) => $bets->sum('amount'));

        // Approved remitted verified collections
        $totalIncomingRemittances = \App\Models\Collection::whereIn('agent_id', $agentIds)
            ->where('is_remitted', true)
            ->where('status', 'approved')
            ->whereNotNull('verified_at')
            ->groupBy('agent_id')
            ->select('agent_id', DB::raw('SUM(net_remit) as total_remit'))
            ->pluck('total_remit', 'agent_id');

        // Pending collections
        $pendingCollections = \App\Models\Collection::with('collectionStubs.bets', 'collectionStubs.deductions')
            ->whereIn('agent_id', $agentIds)
            ->where('status', 'pending')
            ->orderByDesc('collection_date')
            ->get()
            ->groupBy('agent_id');

        // Compute pending remittances and amount due
        $agentsWithPending = $pendingCollections->map(function ($collections, $agentId) use ($agentCommissions) {
            $agent = $collections->first()->agent;
            $totalAmountDue = 0;
            $totalRemittance = 0;

            foreach ($collections as $collection) {
                foreach ($collection->collectionStubs as $stub) {
                    $bets = $stub->bets ?? collect();
                    $deductions = $stub->deductions ?? collect();

                    $gross = $bets->sum('amount');

                    $commissionPercent = 0.10;
                    if ($agentCommissions->has($agent->id)) {
                        $firstGameType = $bets->first()?->game_type;
                        if ($firstGameType) {
                            $found = $agentCommissions[$agent->id]->firstWhere('game_type', $firstGameType);
                            if ($found) $commissionPercent = $found->commission_percent / 100;
                        }
                    }

                    $commission = $gross * $commissionPercent;
                    $amountDue = $gross - $commission;
                    $deductionAmount = $deductions->sum('amount');

                    $netRemittance = $amountDue + $deductionAmount;
                    $totalAmountDue += $amountDue;
                    $totalRemittance += $netRemittance;
                }
            }

            $agent->amount_due = max($totalAmountDue, 0);
            $agent->unpaid_amount = max($totalRemittance, 0);

            return $agent;
        });

        // Compose agent stats
        $agentsWithGross = $agents->map(function ($agent) use (
            $grossSales,
            $totalIncomingRemittances,
            $agentsWithPending,
            $gameDate,
            $agentCommissions,
            $betsByAgent
        ) {
            $agent->gross_sales = $grossSales[$agent->id] ?? 0;
            $agent->total_incoming_remittance = $totalIncomingRemittances[$agent->id] ?? 0;
            $agent->unpaid_amount = $agentsWithPending[$agent->id]->unpaid_amount ?? 0;
            $agent->amount_due = $agentsWithPending[$agent->id]->amount_due ?? 0;

            $agent->collections = \App\Models\Collection::where('agent_id', $agent->id)
                ->whereDate('collection_date', $gameDate)
                ->get();

            // Identify stub_ids already submitted
            $submittedStubIds = DB::table('collection_stub')
                ->join('collections', 'collection_stub.collection_id', '=', 'collections.id')
                ->where('collections.agent_id', $agent->id)
                ->whereDate('collections.collection_date', $gameDate)
                ->pluck('collection_stub.stub_id') 
                ->toArray();

            // Get unsubmitted bets only
            $unsubmittedBets = ($betsByAgent[$agent->id] ?? collect())
                ->filter(fn($bet) => !in_array($bet->stub_id, $submittedStubIds));

            // Net remit from unsubmitted bets (winner = full amount, else deduct commission)
            $agent->net_remit_from_unsubmitted = $unsubmittedBets->sum(function ($bet) use ($agentCommissions, $agent) {
                $commissionPercent = 0.10; // default

                if ($agentCommissions->has($agent->id)) {
                    $agentCommissionForGame = $agentCommissions[$agent->id]->firstWhere('game_type', $bet->game_type);
                    if ($agentCommissionForGame) {
                        $commissionPercent = $agentCommissionForGame->commission_percent / 100;
                    }
                }

                return $bet->is_winner ? $bet->amount : $bet->amount * (1 - $commissionPercent);
            });

            return $agent;
        });

        // Agents with negative net remits (deficits)
        $agentsWithSystemDeficit = $agentsWithGross->filter(function ($agent) {
            $gross = $agent->gross;
            $commission = $agent->commission ?? ($gross * 0.10); // if not manually set
            $incentives = $agent->incentives ?? 0;
            $payouts = $agent->payouts ?? 0;
            $deductions = $agent->deductions ?? 0;

            $computedNetRemit = $gross - $commission - $incentives - $payouts + $deductions;

            return $computedNetRemit < 0; // system deficit condition
        })->map(function ($agent) {
            $gross = $agent->gross;
            $commission = $agent->commission ?? ($gross * 0.10);
            $incentives = $agent->incentives ?? 0;
            $payouts = $agent->payouts ?? 0;
            $deductions = $agent->deductions ?? 0;

            $computedNetRemit = $gross - $commission - $incentives - $payouts + $deductions;

            return [
                'agent' => $agent,
                'system_deficit' => $computedNetRemit, // negative value
            ];
        })->values();


        // Daily totals for cashier agents
        $totalBetsToday = DB::table('bets')
            ->whereIn('agent_id', $agentIds)
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');

        // Total winning bet amount (for payout calculation)
        $totalWinningBetAmount = DB::table('bets')
            ->whereIn('agent_id', $agentIds)
            ->whereDate('created_at', now()->toDateString())
            ->where('is_winner', 1)
            ->sum('amount');

        // Total winnings (amount * multiplier)
        $totalWinnings = DB::table('bets')
            ->join('multipliers', 'bets.game_type', '=', 'multipliers.game_type')
            ->whereIn('bets.agent_id', $agentIds)
            ->whereDate('bets.created_at', now()->toDateString())
            ->where('bets.is_winner', 1)
            ->select(DB::raw('SUM(bets.amount * multipliers.multiplier) as total'))
            ->value('total') ?? 0;

        // Incentives
        $incentives = $totalWinningBetAmount * 3;

        // System deficit check
        $deficitByCashier = 0;
        if ($totalBetsToday < ($totalWinnings + $incentives)) {
            $deficitByCashier = ($totalWinnings + $incentives) - $totalBetsToday;
        }

        return view('cashier.dashboard', [
            'agents' => $agentsWithGross,
            'gameDate' => $gameDate,
            'totalBetsToday' => $totalBetsToday,
            'pendingAgentsCount' => $agentsWithPending->count(),
            'pendingAgents' => $agentsWithPending,
            'totalNetCollected' => 0, // you can compute if needed
            'totalCollected' => $totalIncomingRemittances->sum(),
            // 'agentsWithDeficits' => $agentsWithDeficits,
            'systemDeficit' => $deficitByCashier,
            'agentRemittances' => $agentRemittances,
            'agentsWithSystemDeficit'=> $agentsWithSystemDeficit
        ]);
    }


    public function remittance(Request $request)
    {
        $cashier = auth()->user();

        // Get assigned agent IDs
        $assignedAgentIds = $cashier->assignedAgents()->pluck('id')->toArray();

        // Paid stubs (stub_ids already remitted)
        $paidStubIds = DB::table('receipts')->pluck('stub_id')->toArray();

        // Agents with unpaid stubs
        $agentsWithUnpaidBets = Bet::whereIn('agent_id', $assignedAgentIds)
            ->whereNotIn('stub_id', $paidStubIds)
            ->pluck('agent_id')
            ->unique()
            ->toArray();

        // Bets query
        $betsQuery = Bet::whereIn('agent_id', $agentsWithUnpaidBets);

        // Optional filters
        if ($request->filled('agent_id') && in_array($request->agent_id, $agentsWithUnpaidBets)) {
            $betsQuery->where('agent_id', $request->agent_id);
        }

        if ($request->status === 'paid') {
            $betsQuery->whereIn('stub_id', $paidStubIds);
        } elseif ($request->status === 'unpaid') {
            $betsQuery->whereNotIn('stub_id', $paidStubIds);
        }

        if ($request->filled('start_date')) {
            $betsQuery->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $betsQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $bets = $betsQuery->orderByDesc('created_at')
                        ->paginate(8)
                        ->withQueryString();

        // Agent dropdown options
        $assignedAgents = User::whereIn('id', $agentsWithUnpaidBets)
                            ->orderBy('name')
                            ->get();

        // Remittance Batches
        $batchQuery = RemittanceBatch::with(['agent', 'cashier', 'receipts'])
                        ->where('cashier_id', $cashier->id);

        if ($search = $request->input('search')) {
            $batchQuery->where(function ($q) use ($search) {
                $q->whereHas('agent', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })->orWhere('id', $search);
            });
        }

        if ($request->filled('from')) {
            $batchQuery->whereDate('submitted_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $batchQuery->whereDate('submitted_at', '<=', $request->input('to'));
        }

        $batches = $batchQuery->orderByDesc('submitted_at')->paginate(15);

        // Collections (approved)
        $collectionsQuery = Collection::with([
        'agent',
        'collectionStubs.bets'
        ])
        ->whereIn('agent_id', $assignedAgentIds)
        ->where('status', 'approved');

        // Apply filter: agent
        if ($request->filled('agent_id') && in_array($request->agent_id, $assignedAgentIds)) {
            $collectionsQuery->where('agent_id', $request->agent_id);
        }

        // Apply filter: date range
        if ($request->filled('start_date')) {
            $collectionsQuery->whereDate('collection_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $collectionsQuery->whereDate('collection_date', '<=', $request->end_date);
        }

        $collections = $collectionsQuery
            ->orderByDesc('collection_date')
            ->paginate(8)
            ->withQueryString(); // preserve filters during pagination


        // ✅ NEW: Receipts queued for approval (status = 'pending')
        $queuedReceipts = Receipt::with('agent')
        ->where('status', 'pending') // ensure only pending
        ->orderBy('remitted_at', 'desc')
        ->get();

        
        
        $agents = $cashier->assignedAgents()->get();
        $agentIds = $agents->pluck('id');
        // 7. Fetch pending collections for assigned agents
        $pendingCollections = Collection::with('agent')
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

                    $deduction = method_exists($stub, 'deductions') && $stub->relationLoaded('deductions')
                        ? $stub->deductions->sum('amount')
                        : 0;

                    $netRemittance = $gross - $commission + $deduction;
                    $totalRemittance += $netRemittance;
                }
            }

            $agent->unpaid_amount = max($totalRemittance, 0);
            return $agent;
        });




        // 9. Pass data to view, including filter value
        // return view('cashier.pending-collections', [
        //     'pendingAgentsCount' => $pendingAgents->count(),
        //     'pendingAgents' => $pendingAgents,
        // ]);


        return view('cashier.remittances', compact(
            'bets',
            'assignedAgents',
            'batches',
            'collections',
            'queuedReceipts',
            'collectionsQuery' // ✅ Add this to view
        ));
    }

    public function generateRemittance(Request $request, $agentId)
    {
        
        $request->validate([
            'game_date' => 'required|date',
        ]);

        $gameDate = $request->input('game_date');

        // Ensure winning numbers exist before remittance
        $hasWinningData = DB::table('results')
            ->whereDate('game_date', $gameDate)
            ->exists();

        if (!$hasWinningData) {
            return back()->with('error', 'Admin must input winning numbers for this date before remittance can be generated.');
        }

        // 1. Get unique stubs for this agent on the selected game date
        $stubIds = DB::table('bets')
            ->where('agent_id', $agentId)
            ->whereDate('game_date', $gameDate)
            ->pluck('stub_id')
            ->unique()
            ->toArray();

        if (empty($stubIds)) {
            return back()->with('error', 'No bets found for this agent on that date.');
        }

        // 2. Get all bets for these stubs
        $bets = Bet::whereIn('stub_id', $stubIds)
            ->where('agent_id', $agentId)
            ->whereDate('game_date', $gameDate)
            ->get();

        // 3. Compute remittance details
        $gross = $bets->sum('amount');
        $totalWinnings = $bets->where('is_winner', true)->sum('amount');
        $totalNonWinnings = $bets->where('is_winner', false)->sum('amount');

        $commission = $gross * 0.10;
        $incentives = $totalWinnings * (1 / 3);
        $projectedIncome = $commission + $incentives;
        $netRemit = $gross - $totalWinnings;

        // 4. Check if collection already exists to avoid duplicate
        $alreadyExists = Collection::where('agent_id', $agentId)
            ->whereDate('collection_date', $gameDate)
            ->exists();

        if ($alreadyExists) {
            return back()->with('error', 'Remittance already generated for this agent on this date.');
        }

        // 5. Save to collections table
        $collection = Collection::create([
            'agent_id' => $agentId,
            'collection_date' => $gameDate,
            'gross' => $gross,
            'commission' => $commission,
            'incentives' => $incentives,
            'net_remit' => $netRemit,
            'projected_income' => $projectedIncome,
            'is_remitted' => false,
            'status' => 'pending',
        ]);

        // 6. Attach stubs to pivot table
        foreach ($stubIds as $stubId) {
            DB::table('collection_stub')->insert([
                'collection_id' => $collection->id,
                'stub_id' => $stubId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Remittance successfully generated for Agent ID ' . $agentId);
    }



    public function markPaid(Request $request)
    {
        $cashier = auth()->user();
        $assignedAgentIds = $cashier->assignedAgents()->pluck('id')->toArray();

        // Get all bets from assigned agents that are NOT yet paid (no matching receipt)
        $bets = Bet::with('betAgent')
            ->whereIn('agent_id', $assignedAgentIds)
            ->whereNotIn('stub_id', function ($query) {
                $query->select('stub_id')->from('receipts');
            })
            ->get();

        if ($bets->isEmpty()) {
            return redirect()->back()->with('error', 'No unpaid bets to remit.');
        }

        $totalDue     = $bets->sum('amount');
        $cashTendered = floatval($request->input('cash_tendered', 0));
        $balance      = max(0, $totalDue - $cashTendered);
        $agentId      = $bets->first()->agent_id;

        // Determine payment status based on cash tendered
        $paymentStatus = $cashTendered >= $totalDue ? 'paid' : 'partial';

        // Group bets by stub_id for receipt creation
        $grouped = $bets->groupBy('stub_id');

        // Create a new remittance batch
        $batch = RemittanceBatch::create([
            'agent_id'          => $agentId,
            'cashier_id'        => $cashier->id,
            'total_amount'      => $totalDue,
            'status'            => $paymentStatus,
            'balance'           => $balance,
            'cash_tendered'     => $cashTendered,
            'submitted_at'      => now(),
        ]);

        // Log the balance owed in agent_balances table (if any)
        if ($balance > 0) {
            AgentBalance::create([
                'agent_id'             => $agentId,
                'amount'               => $balance,
                'type'                 => 'debit',
                'note'                 => 'Partial remittance recorded automatically',
                'cashier_id'           => $cashier->id,
                'remittance_batch_id'  => $batch->id,
            ]);
        }

        // Create or update receipts per stub
        foreach ($grouped as $stubId => $group) {
            $total   = $group->sum('amount');
            $agentId = $group->first()->agent_id;

            Receipt::updateOrCreate(
                ['stub_id' => $stubId],
                [
                    'agent_id'            => $agentId,
                    'cashier_id'          => $cashier->id,
                    'total_amount'        => $total,
                    'status'              => $paymentStatus,
                    'remitted_at'         => now(),
                    'remittance_batch_id' => $batch->id,
                ]
            );
        }

        return redirect()
            ->back()
            ->with('success', 'Remittance completed.')
            ->with('remit_balance', $balance)
            ->with('remit_status', $paymentStatus)
            ->with('cash_tendered', $cashTendered)
            ->with('total_due', $totalDue);
    }



    public function receipts(Request $request)
    {
        $cashier = auth()->user();
        $assignedAgentIds = $cashier->assignedAgents()->pluck('id');

        $selectedAgentId = $request->input('agent_id');
        $filterDate = $request->input('filter_date');
        $filterStubId = $request->input('stub_id'); // <-- new line

        $groupedStubs = DB::table('bets')
            ->join('collection_stub', 'bets.stub_id', '=', 'collection_stub.stub_id')
            ->join('collections', 'collection_stub.collection_id', '=', 'collections.id')
            ->select(
                'bets.stub_id',
                'bets.agent_id',
                DB::raw('SUM(bets.amount) as total_amount'),
                DB::raw('COUNT(*) as total_bets'),
                DB::raw('MAX(bets.created_at) as latest')
            )
            ->whereIn('bets.agent_id', $assignedAgentIds)
            ->where('collections.status', 'approved')
            ->when($selectedAgentId, function ($query, $agentId) {
                return $query->where('bets.agent_id', $agentId);
            })
            ->when($filterDate, function ($query) use ($filterDate) {
                return $query->whereDate('bets.created_at', Carbon::parse($filterDate)->toDateString());
            })
            ->when($filterStubId, function ($query) use ($filterStubId) {
                return $query->where('bets.stub_id', $filterStubId); // <-- new filter logic
            })
            ->groupBy('bets.stub_id', 'bets.agent_id')
            ->orderByDesc('latest')
            ->paginate(10);

        $stubIds = collect($groupedStubs->items())->pluck('stub_id')->toArray();

        $representativeBets = Bet::whereIn('stub_id', $stubIds)
            ->orderByDesc('created_at')
            ->get()
            ->keyBy('stub_id');

        $agents = \App\Models\User::whereIn('id', $assignedAgentIds)->get();

        return view('cashier.receipts.index', [
            'stubs' => $groupedStubs,
            'agents' => $agents,
            'selectedAgentId' => $selectedAgentId,
            'representativeBets' => $representativeBets,
            'startDate' => $filterDate,
            'filterStubId' => $filterStubId, // <-- pass to blade
        ]);
    }




    public function showReceipt($stub)
    {
        $cashier = auth()->user();

        // Get agent IDs assigned to this cashier
        $assignedAgentIds = $cashier->assignedAgents()->pluck('id');

        // Get bets belonging to this stub from assigned agents
        $bets = Bet::where('stub_id', $stub)
                    ->whereIn('agent_id', $assignedAgentIds)
                    ->with('agent')
                    ->get();

        if ($bets->isEmpty()) {
            abort(404, 'Receipt not found or unauthorized.');
        }

        $totalAmount = $bets->sum('amount');

        return view('cashier.receipts.show', compact('stub', 'bets', 'totalAmount'));
    }

    public function showRemittanceReceipt($id)
    {
        $cashier = auth()->user();

        // Load collection with agent info
        $collection = Collection::with(['agent'])->findOrFail($id);

        // Check authorization
        if (!$cashier->assignedAgents()->pluck('id')->contains($collection->agent_id)) {
            abort(403, 'Unauthorized.');
        }

        // Get stub_ids linked to this collection
        $stubIds = DB::table('collection_stub')
            ->where('collection_id', $id)
            ->pluck('stub_id');

        // Group bets by stub_id
        $bets = Bet::whereIn('stub_id', $stubIds)->with('agent')->get()->groupBy('stub_id');

        $totalAmount = $bets->flatten()->sum('amount');

        return view('cashier.receipts.show', compact('collection', 'bets', 'totalAmount'));
    
    }


    public function printReceiptsByAgent($agentId)
    {
        $cashier = auth()->user();

        // Verify the agent belongs to the cashier
        if (!$cashier->assignedAgents()->where('id', $agentId)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        // Retrieve all stubs with their bets for this agent
        $stubs = Bet::where('agent_id', $agentId)
                    ->whereNotNull('stub_id')
                    ->groupBy('stub_id')
                    ->get()
                    ->pluck('stub_id');

        $groupedBets = [];
        $totalOverall = 0;

        foreach ($stubs as $stub) {
            $bets = Bet::where('stub_id', $stub)->get();
            $total = $bets->sum('amount');
            $groupedBets[] = [
                'stub_id' => $stub,
                'bets' => $bets,
                'total' => $total,
            ];
            $totalOverall += $total;
        }

        return view('cashier.receipts.multi-print', compact('groupedBets', 'totalOverall'));
    }

    public function exportPdf(Request $request)
    {
        $stubIds = $request->input('stubs', []);
        $cashier = auth()->user();

        if (empty($stubIds)) {
            return redirect()->back()->with('error', 'No stubs selected.');
        }

        // Get assigned agent IDs
        $assignedAgentIds = $cashier->assignedAgents()->pluck('id')->toArray();

        $allReceipts = [];

        foreach ($stubIds as $stub) {
            // Fetch bets for this stub only if they belong to assigned agents
            $bets = Bet::where('stub_id', $stub)
                    ->whereIn('agent_id', $assignedAgentIds)
                    ->get();

            if ($bets->isEmpty()) continue;

            $allReceipts[] = [
                'stub' => $stub,
                'bets' => $bets,
                'total' => $bets->sum('amount'),
            ];
        }

        if (empty($allReceipts)) {
            return redirect()->back()->with('error', 'No valid receipts found or you are not authorized to access them.');
        }

        $pdf = PDF::loadView('cashier.multi-receipt-pdf', compact('allReceipts'));
        return $pdf->download('multi-stub-receipts.pdf');
    }


    public function printReceipt($stub)
    {
         $collection = Collection::with(['agent', 'stubs'])->findOrFail($id);
        $stubBets = Bet::whereIn('stub_id', $collection->stubs->pluck('stub_id'))->get()->groupBy('stub_id');

        $pdf = Pdf::loadView('cashier.remittance.print', compact('collection', 'stubBets'));
        return $pdf->download("remittance_{$collection->id}.pdf");
    }

    public function singlePdf($stub)
    {
        $bets = Bet::where('stub_id', $stub)->get();
        if ($bets->isEmpty()) {
            abort(404, 'Receipt not found.');
        }
        $totalAmount = $bets->sum('amount');

        $pdf = PDF::loadView('cashier.receipt-pdf', compact('bets', 'stub', 'totalAmount'));
        return $pdf->download("receipt_{$stub}.pdf");
    }

    public function fullPdf(Request $request)
    {
        $stubIds = $request->input('stubs', []);
        if (empty($stubIds)) {
            return redirect()->back()->with('error', 'No stubs selected.');
        }

        $allReceipts = [];
        foreach ($stubIds as $stub) {
            $bets = Bet::where('stub_id', $stub)->get();
            if ($bets->isEmpty()) continue;

            $allReceipts[] = [
                'stub' => $stub,
                'bets' => $bets,
                'total' => $bets->sum('amount'),
            ];
        }

        if (empty($allReceipts)) {
            return redirect()->back()->with('error', 'No valid receipts found.');
        }

        $pdf = PDF::loadView('cashier.multi-receipt-pdf', compact('allReceipts'));
        return $pdf->download('full-stub-receipts.pdf');
    }

    public function exportMultipleReceipts(Request $request)
    {
        $stubIds = $request->input('stub_ids', []);
        $cashier = auth()->user();

        // Get only bets belonging to assigned agents and requested stub IDs
        $assignedAgentIds = $cashier->assignedAgents()->pluck('id');

        $bets = Bet::whereIn('stub_id', $stubIds)
            ->whereIn('agent_id', $assignedAgentIds)
            ->orderBy('stub_id')
            ->get()
            ->groupBy('stub_id');

        if ($bets->isEmpty()) {
            return back()->with('error', 'No valid stubs found.');
        }

        $pdf = Pdf::loadView('cashier.receipts.multi-stub-pdf', compact('bets'));
        return $pdf->download('multi-stub-receipts.pdf');
    }

    public function printReceiptStub($stub)
    {
        $cashier = auth()->user();

        // Get all bets under this stub by this cashier's agents
        $agentIds = $cashier->assignedAgents()->pluck('id');
        $bets = Bet::where('stub_id', $stub)->whereIn('agent_id', $agentIds)->with('agent')->get();

        if ($bets->isEmpty()) {
            abort(404, 'No matching stub for your assigned agents.');
        }

        // ✅ Safely get agent name from first bet
        $agent = $bets->first()->betAgent ?? null;
        $agentName = $agent ? $agent->name : 'Unknown Agent';

        $drawDate = $bets->first()->created_at->format('Y-m-d');
        $printedTime = now()->format('Y-m-d h:i:s A');
        $totalAmount = $bets->sum('amount');

        // ✅ Generate QR code
        $qrCodeImage = base64_encode(
            QrCode::format('png')->size(120)->generate($stub)
        );

        return view('cashier.receipts.print-stub', compact(
            'stub',
            'bets',
            'agentName',
            'drawDate',
            'printedTime',
            'totalAmount',
            'qrCodeImage'
        ));
    }

    // Controller Method: reports()

    public function reports(Request $request)
    {
        $cashier = Auth::user();
        $cashierId = $cashier->id;

        $reportType = $request->input('report_type', 'winnings');
        $filterDate = $request->input('filter_date', now()->toDateString());
        $agentId = $request->input('agent_id');

        // Fetch only the agents under the current cashier
        $agents = $cashier->agents;

        $summary = [];

        // Set default date column to use for filtering
        $dateColumn = match ($reportType) {
            'collections' => 'collection_date',
            'winnings'    => 'game_date',
            'balances'    => 'created_at',
            default       => 'created_at',
        };

        // Build the query depending on report type
        $query = match ($reportType) {
            'balances' => AgentBalance::with('agent')->where('cashier_id', $cashierId),

            'collections' => Collection::with('agent')
                ->whereHas('agent', fn($q) => $q->where('cashier_id', $cashierId)),

            'winnings' => Bet::with('betAgent')
                ->where('is_winner', 1)
                ->whereHas('betAgent', fn($q) => $q->where('cashier_id', $cashierId)),

            default => Bet::with('betAgent')
                ->whereHas('agent', fn($q) => $q->where('cashier_id', $cashierId)),
        };

        // Apply date filter
        if ($filterDate) {
            $query->whereDate($dateColumn, $filterDate);
        }

        // Apply agent filter
        if ($agentId) {
            $query->where('agent_id', $agentId);
        }

        // Paginate results
        $reports = $query->orderByDesc($dateColumn)->paginate(10);

        // Prepare summary for collections
        if ($reportType === 'collections') {
            $summary = [
                'gross'       => (clone $query)->sum('gross'),
                'payouts'     => (clone $query)->sum('payouts'),
                'deductions'  => (clone $query)->sum('deductions'),
                'net_remit'   => (clone $query)->sum('net_remit'),
            ];
        }

        if ($reportType === 'balances') {
        $query = AgentBalance::with('agent')
            ->where('cashier_id', $cashierId);

        if ($filterDate) {
            $query->whereDate('created_at', $filterDate);
        }

        if ($agentId) {
            $query->where('agent_id', $agentId);
        }

        $reports = $query->orderByDesc('created_at')->paginate(10);

        $reports->getCollection()->transform(function ($item) {
            $item->amount ??= 0;
            return $item;
        });

        // Default null amount to 0 for balances
        if ($reportType === 'balances') {
            $reports->getCollection()->transform(function ($item) {
                $item->amount ??= 0;
                return $item;
            });
        }
        }


        return view('cashier.reports-template', [
            'reports'         => $reports,
            'agents'          => $agents,
            'summary'         => $summary,
            'reportType'      => $reportType,
            'filterDate'      => $filterDate,
            'selectedAgentId' => $agentId,
        ]);
    }



    // CAshier reports controller
    public function printSummary()
        {
            $reports = Collection::with('agent')->latest()->paginate(100);
            $formattedTitle = 'Remittance Summary as of ' . now()->format('F d, Y');
            
            return view('cashier.reports.print-summary', compact('reports', 'formattedTitle'));
        }

    public function showBatchReceipt(RemittanceBatch $batch)
    {
        $batch->load([
            'receipts' => function ($query) {
                $query->with('bets'); // assuming you have Receipt → hasMany(Bet)
            },
            'agent',
            'cashier',
        ]);

        return view('cashier.receipts.batch-print', compact('batch'));
    }

    public function batchExport(RemittanceBatch $batch)
    {
        $batch->load([
            'receipts' => function ($query) {
                $query->with('bets'); // assuming bets are linked by stub_id
            },
            'agent',
            'cashier',
        ]);

        // Optional: preprocess groupedBets just like in the view
        $groupedBets = $batch->receipts->map(function ($receipt) {
            return [
                'stub_id' => $receipt->stub_id,
                'total'   => $receipt->total_amount,
                'bets'    => \App\Models\Bet::where('stub_id', $receipt->stub_id)->get()
            ];
        });

        $totalOverall = $groupedBets->sum('total');

        $pdf = Pdf::loadView('cashier.receipts.batch-pdf', [
            'batch' => $batch,
            'groupedBets' => $groupedBets,
            'totalOverall' => $totalOverall,
        ])->setPaper('A4');

        return $pdf->download("RemittanceBatch-{$batch->id}.pdf");
    }

    public function remittanceBatches(Request $request)
    {
        $query = RemittanceBatch::with(['agent', 'cashier', 'receipts'])
            ->where('cashier_id', auth()->id());
        if ($search = $request->input('search')) {
            $query->whereHas('agent', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('id', $search);
        }
        if ($request->filled('from')) {
            $query->whereDate('submitted_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('submitted_at', '<=', $request->input('to'));
        }
        $batches = $query->orderByDesc('submitted_at')->paginate(15);

        return view('cashier.remittances', compact('batches'));
    }

    public function pendingCollections()
    {
        $cashier = auth()->user();
        $agentIds = $cashier->assignedAgents()->pluck('id');

        // ✅ Step 1: Fetch pending collections with necessary relationships
        $pendingCollections = Collection::with(['agent', 'collectionStubs.bets', 'collectionStubs.deductions'])
            ->whereIn('agent_id', $agentIds)
            ->where('status', 'pending')
            ->orderByDesc('collection_date')
            ->get();

        $pendingAgents = $pendingCollections->groupBy('agent_id')->map(function ($collections, $agentId) {
        $agent = $collections->first()->agent;
        $collectionDate = $collections->first()->collection_date;

        // Get total gross from stubs
        $gross = $collections->sum(fn($c) =>
            $c->collectionStubs->sum(fn($stub) =>
                $stub->bets->sum('amount'))
        );

        // Check if any winning bets
        $hasWinningBets = $collections->some(fn($c) =>
            $c->collectionStubs->some(fn($stub) =>
                $stub->bets->contains('is_winner', true))
        );

        // Get total winnings (hits/payouts)
        $hits = $collections->sum(fn($c) =>
            $c->collectionStubs->sum(fn($stub) =>
                $stub->bets->where('is_winner', true)->sum('payout'))
        );

        // Get total deductions for the agent on this date
        $deductions = Deduction::where('agent_id', $agentId)
            ->whereDate('deduction_date', $collectionDate)
            ->sum('amount');

        // Compute commission (10% of gross)
        $commission = $gross * 0.10;

        if ($hasWinningBets) {
            // Winning: gross - commission - incentives (1/3 hits) - payouts + deductions
            $incentives = $hits / 3;
            $remit = $gross - $commission - $incentives - $hits + $deductions;
        } else {
            // No winning: gross - commission + deductions
            $remit = $gross - $commission + $deductions;
        }

        $agent->unpaid_amount = $remit;
        $agent->deductions = $deductions;
        $agent->gross = $gross;
        return $agent;
        });


        // ✅ Step 3: Pass $pendingAgents to the Blade view
        return view('cashier.pending-collections', compact('pendingAgents'));
    }

    public function approveAll($agentId)
    {
        $cashier = auth()->user();

        // Fetch pending collections for this agent
        $collections = Collection::where('agent_id', $agentId)
            ->where('status', 'pending')
            ->get();

        if ($collections->isEmpty()) {
            return back()->with('success', 'No pending remittances found.');
        }

        DB::beginTransaction();
        try {
            foreach ($collections as $collection) {
                // Approve collection
                $collection->status = 'approved';
                $collection->verified_at = now();
                $collection->verified_by = $cashier->id;
                $collection->save();

                // Check for deficit
                $expected = $collection->expected_remit;
                $actual = $collection->net_remit;

                if ($expected > $actual) {
                    $deficit = $expected - $actual;

                    AgentBalance::create([
                        'agent_id' => $agentId,
                        'amount' => $deficit,
                        'type' => 'deficit',
                        'note' => "Auto-recorded deficit from remittance approval (Collection #{$collection->id})",
                        'cashier_id' => $cashier->id,
                        'remittance_batch_id' => $collection->remittance_batch_id,
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'All pending remittances approved. Deficit recorded if applicable.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Something went wrong while approving remittances.');
        }
    }

    public function reject($id)
    {
        $collection = Collection::findOrFail($id); // ✅ This line was missing
        $collection->update([
            'status' => 'rejected',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return back()->with('success', 'Remittance rejected.');
    }
    
    // reports sections
    public function exportCashierReportPdf()
    {
        $cashier = auth()->user();

        $agents = $cashier->assignedAgents->map(function ($agent) {
            $gross = $agent->bets()->whereDate('created_at', today())->sum('amount');
            $commission = $gross * 0.10;
            $payouts = $agent->bets()->whereDate('created_at', today())->sum('payout');
            $incentives = $payouts / 3;
            $deductions = $agent->deductions()->whereDate('created_at', today())->sum('amount');
            $remitted = $agent->collections()->whereDate('created_at', today())->sum('net_remit');

            $net_to_remit = $payouts > 0
                ? $gross - $commission - $incentives - $payouts + $deductions
                : $gross - $commission + $deductions;

            return (object)[
                'name' => $agent->name,
                'total_bets' => $gross,
                'gross_sales' => $gross,
                'commission' => $commission,
                'incentives' => $payouts > 0 ? $incentives : 0,
                'payouts' => $payouts,
                'deductions' => $deductions,
                'remitted' => $remitted,
                'net_to_remit' => $net_to_remit,
            ];
        });

        $pdf = Pdf::loadView('reports.pdf-cashier-report', compact('agents'))
                ->setPaper('A4', 'landscape'); // optional: landscape view

        return $pdf->download('cashier-report-' . now()->format('Ymd') . '.pdf');
    }





}
