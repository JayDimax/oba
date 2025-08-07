<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use App\Providers\RouteServiceProvider;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    
    public function username()
    {
        return 'agent_code';
    }
/**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'agent_code' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('agent_code', $request->agent_code)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'agent_code' => 'Invalid credentials.',
            ]);
        }

        // ✅ Remove any previous session for this user
        DB::table('sessions')->where('user_id', $user->id)->delete();

        // ✅ Log the user in
        Auth::login($user);

        // ✅ Regenerate session for security
        $request->session()->regenerate();

        // Optional: Update the session record to include user_id
        DB::table('sessions')->where('id', session()->getId())
            ->update(['user_id' => $user->id]);

        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.admindashboard');
            case 'cashier':
                return redirect()->route('cashier.dashboard');
            case 'agent':
            default:
                return redirect()->route('agent.dashboard');
        }
    }



    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
