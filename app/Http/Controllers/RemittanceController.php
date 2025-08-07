<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RemittanceController extends Controller
{
    public function index(Request $request)
    {
        $cashier = Auth::user();
        $query = Bet::with('agent')
            ->whereHas('agent', fn($q) => $q->where('cashier_id', $cashier->id));

            if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('agent', fn($a) => $a->where('name', 'like', "%{$search}%"))
                ->orWhere('stub_id', 'like', "%{$search}%");
            });
        }

        if ($request->status === 'paid') {
            $query->whereIn('stub_id', function ($q) {
                $q->select('stub_id')->from('receipts');
            });
        } elseif ($request->status === 'unpaid') {
            $query->whereNotIn('stub_id', function ($q) {
                $q->select('stub_id')->from('receipts');
            });
        }


        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $bets = $query->latest()->paginate(20);

        return view('cashier.remittance', compact('bets'));
    }

    // public function updateStatus(Request $request)
    // {
    //     $request->validate([
    //         'bet_ids' => 'required|array',
    //         'status' => 'required|in:paid,partial,unpaid',
    //     ]);
    //     return back()->with('success', 'Payment status updated successfully.');
    // }
}
