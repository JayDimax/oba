<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function toggleActive(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    

    public function index()
    {
        $users = User::whereIn('role', ['agent', 'cashier'])->latest()->paginate(10);
        $cashiers = User::where('role', 'cashier')->get(); // ✅ Add this line
        $latestId = User::max('id') + 1;
        $agentCode = 'DVO-' . str_pad($latestId, 4, '0', STR_PAD_LEFT);
        return view('admin.users.index', compact('users', 'agentCode','cashiers'));
    }

    public function create()
    {
        
        return view('admin.users.create');
    }

     public function edit(User $user)
    {
        
        return view('admin.users.edit', compact('user'));
    }


public function store(Request $request)
{
    // ✅ Step 2.1: Check total count of agents and cashiers
    $maxUsers = config('limits.user_limit');
    $currentUserCount = User::whereIn('role', ['agent', 'cashier'])->count();

    if ($currentUserCount >= $maxUsers) {
        return redirect()->back()->with('error', 'User limit reached. Please upgrade your subscription.');
    }

    // ✅ Step 2.2: Validate input
    $request->validate([
        'name' => 'required|string|max:255',
        'agent_code' => 'required|string|max:50|unique:users',
        'role' => 'required|in:agent,cashier',
    ]);

    // ✅ Step 2.3: Create user
    User::create([
        'name'        => $request->name,
        'email'       => $request->name . '@orcas.com',
        'agent_code'  => $request->agent_code,
        'role'        => $request->role,
        'password'    => Hash::make('password'),
        'is_active'   => true,
    ]);

    return redirect()->back()->with('success', 'User created successfully.');
}


public function update(Request $request, User $user)
{
    $request->validate([
        'name'          => 'required|string|max:255',
        'agent_code'    => 'required|string|max:50|unique:users,agent_code,' . $user->id,
        'role'          => 'required|in:agent,cashier',
        'new_password'  => 'nullable|min:6' // Optional manual input
    ]);

    $data = [
        'name' => $request->name,
        'agent_code' => $request->agent_code,
        'role' => $request->role,
    ];

    // Handle password reset
    if ($request->has('reset_password')) {
        $data['password'] = Hash::make('password');
    }

    // Optional: Allow manual password input
    if ($request->filled('new_password')) {
        $data['password'] = Hash::make($request->new_password);
    }

    $user->update($data);

    return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
}

public function destroy(User $user)
{
    // Optional: also delete related agent record if exists
    if ($user->role === 'agent') {
        $user->agent()->delete(); // if you have the relationship set up
    }

    $user->delete();

    return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
}


public function assignCashier(Request $request, User $agent)
{
    $request->validate([
        'cashier_id' => 'nullable|exists:users,id',
    ]);

    if ($agent->role !== 'agent') {
        return response()->json(['message' => 'Only agents can be assigned a cashier.'], 403);
    }

    if ($request->cashier_id) {
        $cashier = User::where('id', $request->cashier_id)
                       ->where('role', 'cashier')
                       ->first();

        if (!$cashier) {
            return response()->json(['message' => 'Selected user is not a cashier.'], 422);
        }
    }

    $agent->cashier_id = $request->cashier_id;
    $agent->save();

    return response()->json(['message' => 'Cashier assigned successfully.']);
}





}
