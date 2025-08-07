<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Multiplier;
use Illuminate\Http\Request;
use App\Models\AgentCommission;
use App\Models\HotPickLimit;

class SettingController extends Controller
{
    public function index()
    {

        return view('admin.settings.index');
    }

    public function multipliers()
    {
        $multipliers = Multiplier::orderBy('game_type')->paginate(10);
        return view('admin.settings.multipliers', compact('multipliers'));
    }

    public function updateMultipliers(Request $request)
    {
        $request->validate([
            'game_type' => 'required|string',
            'multiplier' => 'required|numeric|min:1',
        ]);

        \App\Models\Multiplier::updateOrCreate(
            ['game_type' => $request->game_type],
            ['multiplier' => $request->multiplier]
        );

        return redirect()->back()->with('success', 'Multiplier updated successfully.');
    }

    public function commissions()
    {
        $agents = User::where('role', 'agent')->get();
        $commissions = AgentCommission::with('agent')->orderBy('updated_at', 'desc')->get();

        return view('admin.settings.commissions', compact('agents', 'commissions'));
    }

    public function updateCommissions(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:users,id',
            'game_type' => 'required|string',
            'commission_percent' => 'required|numeric|min:0',
        ]);

        AgentCommission::updateOrCreate(
            [
                'agent_id' => $request->agent_id,
                'game_type' => $request->game_type,
            ],
            [
                'commission_percent' => $request->commission_percent,
            ]
        );

        return redirect()->back()->with('success', 'Commission updated successfully.');
    }

    public function hotpicks()
    {
        $hotpicks = HotPickLimit::orderBy('updated_at', 'desc')->paginate(5);
        return view('admin.settings.hotpicks', compact('hotpicks'));
    }

    public function updateHotpicks(Request $request)
    {
        $request->validate([
            'game_type' => 'required|string',
            'limit' => 'required|numeric',
        ]);

        HotPickLimit::updateOrCreate(
            ['game_type' => $request->game_type], // search condition
            ['limit' => $request->limit]          // values to update or insert
        );
        
        return redirect()->back()->with('success', 'Hot Pick updated successfully.');
    }

    
}
