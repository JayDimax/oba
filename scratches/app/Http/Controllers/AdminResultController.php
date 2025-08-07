<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bet;
use App\Models\User;
use App\Models\Agent;
use App\Models\Result;
use App\Models\GameSetting;
use Illuminate\Http\Request;
use App\Models\AgentCommission;
use Illuminate\Support\Facades\Log;

class AdminResultController extends Controller
{


    public function index(Request $request)
    {
        // Fetch all results for display
        $today = now()->toDateString(); 
        $bets = Bet::whereDate('game_date', $today)->orderByDesc('created_at')->paginate(10);
        $gameTypes = Result::distinct()->pluck('game_type');
        $gameDraws = Result::distinct()->pluck('game_draw');
        $gameDates = Result::distinct()->pluck('game_date');
        $gameSettings = GameSetting::all();
        // $results = Result::orderBy('game_date', 'desc')->paginate(10)->withQueryString(); // 👈 paginated
        $agents = Agent::all();
        $commissionAgents = User::where('role', 'agent')->with('commissions')->get();

        $results = Result::query();
        if ($request->filled('game_type')){$results->where('game_type', $request->game_type);}
        if ($request->filled('game_draw')){$results->where('game_draw', $request->game_draw);}
        if ($request->filled('game_date')) {$results->whereDate('game_date', $request->game_date);}
        if ($request->filled('winning_combination')){$results->where('winning_combination', 'like', '%' . $request->winning_combination . '%');}
        $results = $results->orderBy('game_date', 'desc')->paginate(10)->appends($request->query());

        return view('admin.results', compact('gameSettings','bets','results','agents','gameTypes', 'gameDraws', 'gameDates','commissionAgents'));
         // create this blade
    }

    public function store(Request $request)
    {
        $drawMap = [
            '14:00' => '14',
            '17:00' => '17',
            '21:00' => '21',
        ];

        $gameDate = Carbon::today()->toDateString();
        $gameType = $request->game_type;
        $gameDraw = $drawMap[$request->game_draw] ?? null;
        $winningNumber = trim($request->winning_combination);

        if (!$gameDraw) {
            return back()->with('error', 'Invalid draw time selected.');
        }

        // Save or update the result
        Result::updateOrCreate(
            [
                'game_type' => $gameType,
                'game_draw' => $gameDraw,
                'game_date' => $gameDate,
            ],
            [
                'winning_combination' => $winningNumber,
            ]
        );

        // Fetch all relevant bets
        $bets = Bet::with('agent') // important to load agent data
            ->where('game_type', $gameType)
            ->where('game_draw', $gameDraw)
            ->whereDate('game_date', $gameDate)
            ->get();

        // Get multiplier from multipliers table
        $multiplier = \App\Models\Multiplier::where('game_type', $gameType)->value('multiplier') ?? 0;

        $winnerCount = 0;

        foreach ($bets as $bet) {
            $isWinner = $bet->bet_number === $winningNumber;

            // Load agent values (fallback to 0)
            $agent = $bet->agent;
            $commissionPercent = $agent?->commission_percent ?? 0;

            // Compute values only for winners
            $winnings = $isWinner ? $bet->amount * $multiplier : null;
            $commissionBase = $isWinner ? $bet->amount * ($commissionPercent / 100) : null;
            $commissionBonus = $isWinner ? $this->calculateIncentive($bet->amount, $gameType) : null;

            $bet->update([
                'is_winner' => $isWinner,
                'winnings' => $winnings,
                'multiplier' => $isWinner ? $multiplier : null,
                'commission_base' => $commissionBase,
                'commission_bonus' => $commissionBonus,
            ]);

            if ($isWinner) {
                $winnerCount++;
                Log::info("Bet ID {$bet->id} marked as WINNER.");
            }
        }

        Log::info("Declared result for $gameType ($gameDraw) on $gameDate. Total winners: $winnerCount");

        return redirect()->back()->with('success', "Result declared! Winners: $winnerCount");
    }


    private function calculateIncentive($amount, $gameType)
    {
        // Example formula: 30% of one-third of bet amount
        return ($amount / 10) * 30; 
    }





    public function updateMultipliers(Request $request)
    {
        foreach ($request->settings as $id => $data) {
            GameSetting::where('id', $id)->update([
                'multiplier' => $data['multiplier'],
                'bonus_per_10' => $data['bonus_per_10'],
            ]);
        }

        return back()->with('success', 'Game multipliers and bonuses updated successfully.');
    }

    public function updateAgentCommissions(Request $request)
    {
        foreach ($request->commissions as $agentId => $gameTypes) {
            foreach ($gameTypes as $gameType => $percent) {
                AgentCommission::updateOrCreate(
                    ['agent_id' => $agentId, 'game_type' => $gameType],
                    ['commission_percent' => $percent]
                );
            }
        }

        return back()->with('success', 'Agent commission rates updated successfully.');
    }

    public function delete($id)
    {
        $result = Result::findOrFail($id);
        $result->delete();

        return redirect()->back()->with('success', 'Result deleted successfully.');
    }
    public function edit($id)
    {
        $result = Result::findOrFail($id);
        return view('admin.results.edit', compact('result'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'game_type' => 'required|string|in:L2,S3,4D',
            'game_draw' => 'required|date_format:H:i',
            'winning_combination' => 'required|string|max:10',
        ]);

        $result = Result::findOrFail($id);
        $result->update([
            'game_type' => $request->input('game_type'),
            'game_draw' => $request->input('game_draw'),
            'winning_combination' => $request->input('winning_combination'), 
        ]);

        return redirect()->route('admin.results.store')
            ->with('success', 'Result updated successfully.');
    }


}
