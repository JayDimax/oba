<?php

namespace App\Services;

use Illuminate\Support\Collection;

class RemittanceCalculator
{
    public static function computeNetRemit(Collection $bets, $manualOverride = null): array
    {
        $gross = $bets->sum('amount');
        $commission = $gross * 0.10;

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

