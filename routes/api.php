<?php

use App\Models\Agent;
use Illuminate\Http\Request;
use App\Models\CollectionStub;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/agent/{agentId}/printer-mac', function ($agentId) {
        $agent = Agent::find($agentId);
        if (!$agent || !$agent->printer_mac) {
            return response()->json(['error' => 'Printer MAC not found'], 404);
        }
        return response()->json(['printerMac' => $agent->printer_mac]);
    });

    Route::get('/agent/receipt/{stubId}', function ($stubId) {
        $stub = CollectionStub::with('bets', 'agent')->find($stubId);
        if (!$stub) {
            return response()->json(['error' => 'Receipt not found'], 404);
        }

        // Prepare data in the format your app expects
        return response()->json([
            'agentName' => $stub->agent->name,
            'drawDate' => $stub->draw_date->format('Y-m-d'),
            'stub' => $stub->id,
            'bets' => $stub->bets->map(function ($bet) {
                return [
                    'draw' => $bet->game_draw,
                    'game' => $bet->game_type,
                    'combi' => $bet->bet_number,
                    'amount' => $bet->amount,
                ];
            }),
            'totalAmount' => $stub->bets->sum('amount'),
            'printedTime' => now()->format('Y-m-d H:i:s'),
        ]);
    });

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

        return response()->json(['token' => $token]);
    });
});