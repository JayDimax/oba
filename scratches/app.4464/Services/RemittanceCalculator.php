<?php

namespace App\Services;

use Illuminate\Support\Collection;

class RemittanceCalculator
{
    public static function computeNetRemit(Collection $bets, $manualOverride = null): array
    {
        $gross = $bets->sum('amount');
        $commission = $gross * 0.10;

        $hasWinners = $bets->contains(fn($b) => $b->is_winner === true);

        $payouts = $hasWinners
            ? $bets->where('is_winner', true)->sum(fn($b) => $b->winnings ?? 0)
            : 0;

        $incentives = $hasWinners
            ? $bets->where('is_winner', true)->sum(fn($b) => $b->amount ?? 0) * (1 / 3)
            : 0;

        $computed = $hasWinners
            ? $gross - $commission - $incentives - $payouts
            : $gross - $commission;

        return [
            'gross' => round($gross, 2),
            'commission' => round($commission, 2),
            'incentives' => round($incentives, 2),
            'payouts' => round($payouts, 2),
            'projected_income' => round($commission + $incentives, 2),
            'computed' => round(max($computed, 0), 2),
            'final' => round($manualOverride ?? max($computed, 0), 2),
        ];
    }
}

