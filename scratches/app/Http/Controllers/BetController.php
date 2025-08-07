<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\Agent;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\AgentBlock;

class BetController extends Controller
{
    

public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'bets' => 'required|array',
            'bets.*.stub_id' => 'required|string',
            'bets.*.game_type' => 'required|string',
            'bets.*.game_draw' => 'required|string',
            // 'bets.*.game_date' => 'required|date', // Remove this — we will compute game_date
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

        // Compute once and use for all bets
        $now = Carbon::now('Asia/Manila');
        $gameDate = $now->hour >= 21
            ? $now->copy()->addDay()->toDateString()
            : $now->toDateString();

        foreach ($validated['bets'] as $bet) {
            $savedBets[] = Bet::create([
                'stub_id' => $bet['stub_id'],
                'game_type' => $bet['game_type'],
                'game_draw' => $bet['game_draw'],
                'game_date' => $gameDate, // <-- Injected
                'bet_number' => $bet['bet_number'],
                'amount' => $bet['amount'],
                'agent_id' => $agentId,
                'is_winner' => 0,
                'winnings' => 0,
            ]);
        }

        Log::info('Saved Bets Count: ' . count($savedBets));
        foreach ($savedBets as $b) {
            Log::info('Saved Bet:', $b->toArray());
        }

        return response()->json([
            'success' => true,
            'bets' => $savedBets,
            'game_date_used' => $gameDate,
        ]);

    } catch (\Exception $e) {
        Log::error('Bet store error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Server error',
            'error' => $e->getMessage(),
        ], 500);
    }
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
