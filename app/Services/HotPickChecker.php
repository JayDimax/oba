<?php

namespace App\Services;

use App\Models\HotPickLimit;
use App\Models\Bet;
use Carbon\Carbon;

class HotPickChecker
{
    /**
     * Check if a combination is already locked as Hot Pick
     *
     * @param string $gameType   // e.g., 'L2', 'S3', '4D'
     * @param string $gameDraw   // e.g., '2PM', '5PM', '9PM'
     * @param string $betNumber  // e.g., '12', '123', etc.
     * @return bool
     */
    public static function isHotPick(string $gameType, string $gameDraw, string $betNumber): bool
    {
        $now = Carbon::now('Asia/Manila');
        $gameDate = $now->hour >= 21
            ? $now->copy()->addDay()->toDateString()
            : $now->toDateString();

        $limit = HotPickLimit::where('game_type', $gameType)->value('limit');

        if (!$limit) {
            return false; // No limit set, treat as not locked
        }

        $currentCount = Bet::where('game_type', $gameType)
            ->where('game_draw', $gameDraw)
            ->where('game_date', $gameDate)
            ->where('bet_number', $betNumber)
            ->count();

        return $currentCount >= $limit;
    }
}
