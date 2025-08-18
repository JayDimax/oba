<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bet;
use App\Models\Agent;
use App\Models\AgentBlock;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CollectionStub;
use App\Services\HotPickChecker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BetController extends Controller
{
    

public function store(Request $request)
{
    DB::beginTransaction();

    try {
        $validated = $request->validate([
            'bets' => 'required|array',
            'bets.*.stub_id' => 'required|string',
            'bets.*.game_type' => 'required|string',
            'bets.*.game_draw' => 'required|string',
            'bets.*.bet_number' => 'required|string',
            'bets.*.amount' => 'required|numeric|min:1',
        ]);

        $agent = auth()->user();
        if (!$agent->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact admin.',
            ], 403);
        }

        $agentId = $agent->id;
        $savedBets = [];

        $now = Carbon::now('Asia/Manila');
        $gameDate = $now->hour >= 21
            ? $now->copy()->addDay()->toDateString()
            : $now->toDateString();

        foreach ($validated['bets'] as $bet) {
            $isHotPick = HotPickChecker::isHotPick(
                $bet['game_type'],
                $bet['game_draw'],
                $bet['bet_number']
            );

            if ($isHotPick) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "❌ Combination {$bet['bet_number']} is LOCKED for {$bet['game_type']} today. Please choose another number.",
                ], 422);
            }

            $savedBets[] = Bet::create([
                'stub_id' => $bet['stub_id'],
                'game_type' => $bet['game_type'],
                'game_draw' => $bet['game_draw'],
                'game_date' => $gameDate,
                'bet_number' => $bet['bet_number'],
                'amount' => $bet['amount'],
                'agent_id' => $agentId,
                'is_winner' => 0,
                'winnings' => 0,
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'bets' => $savedBets,
            'game_date_used' => $gameDate,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Bet store error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Submission failed. Please try again.',
        ], 500);
    }
}

public function checkHotPick(Request $request)
{
    $request->validate([
        'bet_number' => 'required|string',
        'game_type' => 'required|string',
        'game_draw' => 'required|string',
    ]);

    $isHotPick = HotPickChecker::isHotPick(
        $request->game_type,
        $request->game_draw,
        $request->bet_number
    );

    return response()->json(['locked' => $isHotPick]);
}

public function showReceipt($stub)
{
    $agentId = auth()->id();

    // Fetch bets for the stub and agent
    $bets = Bet::where('stub_id', $stub)
        ->where('agent_id', $agentId)
        ->get();

    // Handle missing receipt
    if ($bets->isEmpty()) {
        abort(404, 'Receipt not found.');
    }

    // Extract data from first bet
    $firstBet = $bets->first();
    $agentName = optional($firstBet->betAgent)->name ?? 'Unknown Agent';
    $drawDate = $firstBet->game_date ?? now()->format('Y-m-d');
    $printedTime = now()->format('Y-m-d h:i A');
    $totalAmount = $bets->sum('amount');

    // Generate QR Code as SVG
    $qrCodeSvg = QrCode::format('svg')
        ->size(72)
        ->generate("Stub-{$stub}");

    // Return receipt view
    return view('agent.prints.agent-receipt', compact(
        'bets', 'totalAmount', 'stub', 'drawDate', 'printedTime', 'qrCodeSvg', 'agentName'
    ));
}

public function showReceiptJson($stub)
{
    $agentId = auth()->id();

    $bets = Bet::where('stub_id', $stub)
        ->where('agent_id', $agentId)
        ->get();

    if ($bets->isEmpty()) {
        return response()->json(['error' => 'Receipt not found'], 404);
    }

    $firstBet = $bets->first();
    $agentName = optional($firstBet->betAgent)->name ?? 'Unknown Agent';
    $drawDate = $firstBet->game_date ?? now()->format('Y-m-d');
    $printedTime = now()->format('Y-m-d H:i A');
    $totalAmount = $bets->sum('amount');

    $betArray = $bets->map(function ($bet) {
        return [
            'game_type'  => $bet->game_type,
            'draw_time'  => $bet->game_draw,
            'numbers'    => $bet->bet_number,
            'amount'     => (float) $bet->amount,
        ];
    });

    return response()->json([
        'stub_id'      => $stub,
        'agentName'    => $agentName,
        'drawDate'     => $drawDate,
        'bets'         => $betArray,
        'totalAmount'  => (float) $totalAmount,
        'printedTime'  => $printedTime,
    ], 200, [], JSON_PRETTY_PRINT);
}

public function showReceiptsJsonMulti(Request $request)
{
    $stubs = $request->input('stubs', []); // array of stub IDs
    $agentId = auth()->id();

    $receipts = [];

    foreach ($stubs as $stub) {
        $bets = Bet::where('stub_id', $stub)
            ->where('agent_id', $agentId)
            ->get();

        if ($bets->isEmpty()) continue;

        $firstBet = $bets->first();
        $agentName = optional($firstBet->betAgent)->name ?? 'Unknown Agent';
        $drawDate = $firstBet->game_date ?? now()->format('Y-m-d');
        $printedTime = now()->format('Y-m-d H:i A');
        $totalAmount = $bets->sum('amount');

        $betArray = $bets->map(function ($bet) {
            return [
                'game_type'  => $bet->game_type,
                'draw_time'  => $bet->game_draw,
                'numbers'    => $bet->bet_number,
                'amount'     => (float) $bet->amount,
            ];
        });

        $receipts[] = [
            'stub_id'     => $stub,
            'agentName'   => $agentName,
            'drawDate'    => $drawDate,
            'bets'        => $betArray,
            'totalAmount' => (float) $totalAmount,
            'printedTime' => $printedTime,
        ];
    }

    return response()->json($receipts, 200, [], JSON_PRETTY_PRINT);
}

public function jsonMulti(Request $request)
{
    // Expect multiple stub_ids from frontend like ?stub_ids=123,456,789
    $stubIds = explode(',', $request->query('stub_ids'));

    Log::info("📥 jsonMulti called", ['stub_ids' => $stubIds]);

    $stubs = Bet::whereIn('stub_id', $stubIds)->get();

    if ($stubs->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No stubs found for given IDs',
        ], 404);
    }

    // Group by stub_id for receipt
    $grouped = $stubs->groupBy('stub_id')->map(function ($items) {
        return [
            'agent_code' => $items->first()->agent_code ?? null,
            'draw_time'  => $items->first()->game_draw ?? null,
            'bets' => $items->map(function ($bet) {
                return [
                    'game'   => $bet->game_type,
                    'number' => $bet->number,
                    'amount' => $bet->amount,
                ];
            })->values(),
        ];
    });

    return response()->json([
        'success' => true,
        'stubs'   => $grouped,
    ]);
}
    
public function printMulti($stubIds)
{
    Log::info("📥 printMulti called", ['stub_ids' => $stubIds]);

    // Convert comma-separated IDs to array
    $ids = explode(',', $stubIds);

    Log::info("🔍 Searching stubs", ['ids' => $ids]);

    // Fetch bets with agent name
    $bets = Bet::select('bets.*', 'agents.name as agent_name')
        ->join('agents', 'agents.id', '=', 'bets.agent_id')
        ->whereIn('bets.stub_id', $ids)
        ->orderBy('bets.stub_id')
        ->get();

        dd($bets);

    if ($bets->isEmpty()) {
        Log::warning("⚠️ No bets found for given stubs", ['ids' => $ids]);
        return response()->json(['error' => 'No bets found'], 404);
    }

    // Group by stub_id
    $grouped = $bets->groupBy('stub_id');

    Log::info("🧾 Grouped bets", ['count' => $grouped->count()]);

    return response()->json([
        'status' => 'success',
        'stubs'  => $grouped
    ]);
}

public function preview(Request $request)
{
    try {
        $stubIds = json_decode($request->input('stubs'), true);
        $bets = Bet::whereIn('stub_id', $stubIds)->get();

        if ($bets->isEmpty()) {
            Log::error('Preview Stub Error: No bets found.');
            return back()->withErrors(['preview' => 'No bets found.']);
        }

        $grouped = $bets->groupBy(function ($bet) {
            return $bet->game_type . '-' . $bet->game_draw;
        });

        $totalAmount = $bets->sum('amount');
        $agentName = auth()->user()->name ?? 'Unknown Agent';
        $printedTime = now()->format('Y-m-d H:i:s');
        $drawDate = $bets->first()->game_date ?? now()->toDateString(); // fallback if empty

        $qrContent = request('qr') ?? 'STUBS:N/A';
        $qrCodeImage = QrCode::format('png')->size(90)->generate($qrContent);
        

        return view('agent.receipt-preview', compact('grouped', 'drawDate','totalAmount', 'qrCodeImage', 'agentName', 'printedTime','bets'));
    } catch (\Exception $e) {
        Log::error('Preview Stub Error: ' . $e->getMessage());
        return back()->withErrors(['preview' => 'Something went wrong.']);
    }
}

protected function isAgentBlockedForDraw($agentId, $drawLabel)
{
    return AgentBlock::where('agent_id', $agentId)
        ->where('blocked_draw', $drawLabel)
        ->exists();
}

}
