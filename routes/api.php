<?php

use App\Models\User;
use App\Models\Agent;
use Illuminate\Http\Request;
use App\Models\PrinterDevice;
use App\Models\CollectionStub;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BetController;
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
// Register Android device
Route::post('/register-device', function (Request $request) {
    $request->validate([
        'agent_id' => 'required|exists:agents,id',
        'device_ip' => 'required|ip',
        'printer_mac' => 'required',
    ]);

    PrinterDevice::updateOrCreate(
        ['agent_id' => $request->agent_id],
        [
            'device_ip' => $request->device_ip,
            'printer_mac' => $request->printer_mac,
            'device_name' => $request->device_name,
            'last_seen' => now(),
            'is_online' => true
        ]
    );

    return response()->json(['status' => 'registered']);
});

// Print single receipt (auto-sends to agent's Android)
Route::get('/agent/print-receipt/{stubId}', function ($stubId) {
    $stub = CollectionStub::with('bets', 'agent')->find($stubId);
    if (!$stub) {
        return response()->json(['error' => 'Receipt not found'], 404);
    }

    $device = PrinterDevice::where('agent_id', $stub->agent_id)->first();
    if (!$device) {
        return response()->json(['error' => 'No printer registered for this agent'], 503);
    }

    $data = [
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
        })->toArray(),
        'totalAmount' => $stub->bets->sum('amount'),
        'printedTime' => now()->format('Y-m-d H:i:s'),
    ];

    $url = "http://{$device->device_ip}:8080/print";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return response()->json(['status' => 'printed']);
    } else {
        return response()->json(['status' => 'failed', 'device' => $device->device_ip], 500);
    }
});

// Print multiple receipts (grouped by agent)
Route::post('/agent/print-multiple', function (Request $request) {
    $stubIds = $request->input('stub_ids');
    $stubs = CollectionStub::with('bets', 'agent')->whereIn('id', $stubIds)->get();

    if ($stubs->isEmpty()) {
        return response()->json(['error' => 'No valid receipts found'], 404);
    }

    $byAgent = $stubs->groupBy('agent_id');
    $results = [];

    foreach ($byAgent as $agentId => $agentStubs) {
        $device = PrinterDevice::where('agent_id', $agentId)->first();
        if (!$device) {
            $results[$agentId] = ['status' => 'failed', 'reason' => 'no_printer'];
            continue;
        }

        $receipts = $agentStubs->map(function ($stub) {
            return [
                'agentName'   => $stub->agent->name,
                'drawDate'    => $stub->draw_date->format('Y-m-d'),
                'stub'        => $stub->id,
                'bets'        => $stub->bets->map(fn($bet) => [
                    'draw'   => $bet->game_draw,
                    'game'   => $bet->game_type,
                    'combi'  => $bet->bet_number,
                    'amount' => $bet->amount,
                ])->toArray(),
                'totalAmount' => $stub->bets->sum('amount'),
                'printedTime' => now()->format('Y-m-d H:i:s'),
            ];
        });

        $url = "http://{$device->device_ip}:8080/print-multiple";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['receipts' => $receipts->toArray()]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $results[$agentId] = $httpCode === 200 ? ['status' => 'printed'] : ['status' => 'failed'];
    }

    return response()->json(['results' => $results]);
});

Route::get('/print-stub/{stubId}', [BetController::class, 'printStub']);
Route::middleware(['auth'])->get('/agent/receipts/json-merged', [BetController::class, 'showReceiptsJsonMerged'])
     ->name('agent.receipts.jsonMerged');
     
Route::get('/print-multi/{stub_ids}', [BetController::class, 'printMulti']);