<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AgentRegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.agent-register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $agentCode = User::generateAgentCode('DVO'); // customize region if needed

        $user = User::create([
            'name' => $request->name,
            'agent_code' => $agentCode,
            'password' => Hash::make($request->password),
            'role' => 'agent',
        ]);

        Auth::login($user);
        return redirect()->route('dashboard.agent');
    }
}
