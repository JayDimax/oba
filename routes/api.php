<?php

use App\Models\Agent;
use App\Models\User;
use App\Models\CollectionStub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// ----------------------------------------
// Public routes (no token required)
// ----------------------------------------
Route::post('/login', function (Request $request) {
    $request->validate([
        'agent_code' => 'required|string',
        'password' => 'required',
    ]);

    $user = User::where('agent_code', $request->agent_code)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'agent_code' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'agentId' => $user->id,
        'agentName' => $user->name
    ]);
});


// ----------------------------------------
// Protected routes (require Sanctum token)
// ----------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Get printer MAC for logged-in agent
    Route::get('/agent/printer-mac', function (Request $request) {
        $agent = $request->user();
        if (!$agent->printer_mac) {
            return response()->json(['error' => 'Printer MAC not set'], 404);
        }
        return response()->json([
            'mac' => $agent->printer_mac,
            'agentName' => $agent->name,
        ]);
    });

    // Get receipt data
    Route::get('/agent/receipt/{stubId}', function ($stubId) {
        $stub = CollectionStub::with('bets', 'agent')->find($stubId);
        if (!$stub) {
            return response()->json(['error' => 'Receipt not found'], 404);
        }

        return response()->json([
            'agentName'   => $stub->agent->name,
            'drawDate'    => $stub->draw_date->format('Y-m-d'),
            'stub'        => $stub->id,
            'bets'        => $stub->bets->map(function ($bet) {
                return [
                    'draw'   => $bet->game_draw,
                    'game'   => $bet->game_type,
                    'combi'  => $bet->bet_number,
                    'amount' => $bet->amount,
                ];
            }),
            'totalAmount' => $stub->bets->sum('amount'),
            'printedTime' => now()->format('Y-m-d H:i:s'),
        ]);
    });

    // Optional: logout
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    });
});
