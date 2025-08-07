<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agent;
use Illuminate\Http\Request;

class AdminAgentController extends Controller
{
    public function index()
    {
         $users = Agent::paginate(15); // or however you get agents
        $cashiers = User::where('role', 'cashier')->get();

        return view('admin.users.index', compact('users', 'cashiers'));





       
    }

    public function edit(Agent $agent)
    {
        return view('admin.agents.edit', compact('agent'));
    }

    public function update(Request $request, Agent $agent)
    {
        $request->validate([
            'multiplier' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0|max:100',
        ]);

        $agent->update([
            'multiplier' => $request->multiplier,
            'commission' => $request->commission,
        ]);

        return redirect()->route('admin.agents.index')->with('success', 'Agent settings updated.');
    }
   public function assignCashier(Request $request, Agent $agent)
    {
        $request->validate([
            'cashier_id' => 'nullable|exists:users,id',
        ]);

        $agent->cashier_id = $request->cashier_id;
        $agent->save();

        return response()->json(['message' => 'Cashier assigned successfully.']);
    }
}

