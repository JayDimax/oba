<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Bet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReceiptController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'stub_ids' => 'required|array',
            'stub_ids.*' => 'required|string|exists:bets,stub_id',
        ]);

        $cashierId = Auth::id();
        $createdReceipts = [];

        foreach (array_unique($validated['stub_ids']) as $stubId) {
            // Prevent duplicate receipts for same stub
            if (!Receipt::where('stub_id', $stubId)->exists()) {
                $createdReceipts[] = Receipt::create([
                    'stub_id' => $stubId,
                    'cashier_id' => $cashierId,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'receipts' => $createdReceipts,
        ]);
    }
}

