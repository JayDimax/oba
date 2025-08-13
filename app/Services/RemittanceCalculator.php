<?php

namespace App\Services;

use App\Models\AgentCommission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RemittanceCalculator
{
    public static function computeNetRemit(Collection $bets, $manualOverride = null): array
    {
        $gross = $bets->sum('amount');
        $commission = 0;

        // Preload all commission rates into a key-value map
        $commissionRates = AgentCommission::whereIn('agent_id', $bets->pluck('agent_id')->unique())
            ->whereIn('game_type', $bets->pluck('game_type')->unique())
            ->get()
            ->keyBy(fn($row) => $row->agent_id . '-' . $row->game_type)
            ->toArray(); // force to array


        $bets->groupBy(['agent_id', 'game_type'])->each(function ($group, $agentId) use (&$commission, $commissionRates) {
            foreach ($group as $gameType => $betsGroup) {
                $key = $agentId . '-' . $gameType;
                $rate = isset($commissionRates[$key]) ? $commissionRates[$key]['commission_percent'] : 0; // fallback, log if needed
                $percent = $rate / 100;
                $groupGross = $betsGroup->sum('amount');
                $commission += $groupGross * $percent;
            }
        });
        

        $hasWinners = $bets->contains(fn($b) => (int) $b->is_winner === 1);

        $totalWinnings = $hasWinners
            ? $bets->filter(fn($b) => $b->is_winner)->sum('winnings')
            : 0;

        $payouts = $hasWinners ? $totalWinnings : 0;
        $incentives = 0;

        $computed = $hasWinners
            ? $gross - $commission - $payouts
            : $gross - $commission;

        return [
            'gross' => round($gross, 2),
            'commission' => round($commission, 2),
            'incentives' => round($incentives, 2),
            'payouts' => round($payouts, 2),
            'totalWinnings' => round($totalWinnings, 2),
            'projected_income' => round($commission, 2),
            'computed' => round(max($computed, 0), 2),
            'final' => round($manualOverride ?? max($computed, 0), 2),
        ];
    }

    public static function calculateCommissionForBetsByAgent($bets, int $agentId): float
    {
        if ($bets->isEmpty()) {
            return 0;
        }

        // Fetch commission rates for the agent, keyed by uppercase game_type
        $commissionRates = \App\Models\AgentCommission::where('agent_id', $agentId)
            ->pluck('commission_percent', 'game_type')
            ->mapWithKeys(fn($percent, $gameType) => [strtoupper($gameType) => $percent])
            ->toArray();

        // Group bets by uppercase game_type
        $groupedBets = $bets->groupBy(fn($bet) => strtoupper($bet->game_type));

        $totalCommission = 0;

        foreach ($groupedBets as $gameType => $betsGroup) {
            $gross = $betsGroup->sum('amount');
            $rate = $commissionRates[$gameType] ?? 0;
            $totalCommission += $gross * ($rate / 100);
        }

        return $totalCommission;
    }



}

