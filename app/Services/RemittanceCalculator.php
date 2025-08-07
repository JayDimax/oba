<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\AgentCommission;

class RemittanceCalculator
{
    public static function computeNetRemit(Collection $bets, $manualOverride = null): array
    {
        $gross = $bets->sum('amount');
        $commission = 0;

        // 1. Group bets by agent_id and game_type to apply commission per rule
        $bets->groupBy(['agent_id', 'game_type'])->each(function ($group, $agentId) use (&$commission) {
            foreach ($group as $gameType => $betsGroup) {
                // Fetch commission percent from agent_commissions table
                $percent = AgentCommission::where('agent_id', $agentId)
                    ->where('game_type', $gameType)
                    ->value('commission_percent') ?? 0.10; // fallback to 10% if not set
                $percent = $percent / 100;
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

}

