<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\Claim;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function storeClaim(Request $request)
{
    $betId = $request->input('bet_id');

    // Get the bet
    $bet = Bet::where('id', $betId)
        ->where('is_winner', 1)
        ->firstOrFail();

    // Compute total winnings based on bet amount and multiplier
    $totalWinnings = $bet->amount * $bet->multiplier;

    // Save the claim
    $claim = new Claim();
    $claim->bet_id = $bet->id;
    $claim->agent_id = auth()->id(); // or from request
    $claim->claimed_at = now();
    $claim->total_winnings = $totalWinnings;
    $claim->save();

    return redirect()->back()->with('success', 'Claim saved successfully.');
}
}
