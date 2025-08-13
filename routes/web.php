<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\{
    BetController,
    UserController,
    AgentController,
    CashierController,
    CollectionController,
    ProfileController,
    SettingController,
    DashboardController,
    AdminAgentController,
    AdminResultController,
    Auth\AgentRegisteredUserController
};
use App\Models\Dashboard;

// Public Landing Page
Route::get('/', fn () => view('welcome'))->name('welcome');

// Agent Registration (Guest)
Route::get('/register/agent', [AgentRegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('agent.register');

Route::post('/register/agent', [AgentRegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('agent.register.store');

// Default Dashboard with role-based redirect
Route::get('/dashboard', function () {
    $user = auth()->user();

    if (!$user) {
        return redirect()->route('login');
    }

    switch ($user->role) { 
        case 'agent':
            return redirect()->route('agent.dashboard');
        case 'cashier': 
            return redirect()->route('cashier.dashboard');
        case 'admin':
        default:
            return view('admin.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile Routes (Authenticated)
Route::middleware('auth')->group(function () {
    // Shared profile view/edit routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Role-specific profile updates
    Route::get('/profile/admin', [ProfileController::class, 'adminedit'])->name('profile.admin-edit');
    Route::patch('/profile/admin', [ProfileController::class, 'adminupdate'])->name('profile.admin-update');
    Route::patch('/profile/agent', [ProfileController::class, 'agentupdate'])->name('profile.agent-update');
});


// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('admindashboard');
    

    Route::post('/register/agent', [AgentRegisteredUserController::class, 'store'])->name('agent.register.store'); 

    // User management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggleActive');

    // Results
    Route::get('/results', [AdminResultController::class, 'index'])->name('results.index');
    Route::post('/results', [AdminResultController::class, 'store'])->name('results.store');
    Route::get('/results/{id}/edit', [AdminResultController::class, 'edit'])->name('results.edit');
    Route::delete('/results/{id}', [AdminResultController::class, 'delete'])->name('results.delete');
    Route::put('/results/{id}', [AdminResultController::class, 'update'])->name('results.update');

    // Agents
    Route::get('/agents', [AdminAgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/{agent}/edit', [AdminAgentController::class, 'edit'])->name('agents.edit');
    Route::post('/agents/{agent}', [AdminAgentController::class, 'update'])->name('agents.update');
    Route::post('/agents/{agent}/assign-cashier', [UserController::class, 'assignCashier'])->name('agents.assignCashier');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/settings/multipliers', [SettingController::class, 'multipliers'])->name('settings.multipliers');
    Route::post('/settings/multipliers/update', [SettingController::class, 'updateMultipliers'])->name('settings.multipliers.update');
    Route::get('/commissions', [SettingController::class, 'commissions'])->name('settings.commissions');
    Route::post('/commissions', [SettingController::class, 'updateCommissions'])->name('settings.commissions.update');
    Route::get('/hotpicks', [SettingController::class, 'hotpicks'])->name('settings.hotpicks');
    Route::post('/hotpicks/update', [SettingController::class, 'updateHotpicks'])->name('settings.hotpicks.update');

    Route::post('/multipliers/update', [AdminResultController::class, 'updateMultipliers'])->name('multipliers.update');
    Route::post('/agent-commissions/update', [AdminResultController::class, 'updateAgentCommissions'])->name('agent-commissions.update');

    // Admin Reports
    Route::get('/reports', [DashboardController::class, 'index'])->name('reports.index');
    Route::get('/admin/reports/daily', [DashboardController::class, 'dailyReport'])->name('reports.daily');
    Route::get('/admin/reports/monthly', [DashboardController::class, 'monthlyReport'])->name('reports.monthly');
    Route::get('/admin/reports/yearly', [DashboardController::class, 'yearlyReport'])->name('reports.yearly');
    Route::get('/print-report', [DashboardController::class, 'printReport'])->name('reports.print');
    Route::get('/print-stubs', [DashboardController::class, 'printStubs'])->name('print');
    Route::get('/export-bets', [DashboardController::class, 'exportBets'])->name('export-bets');
});

// Cashier Routes
Route::middleware(['auth', 'role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {

    Route::get('/dashboard', [CashierController::class, 'dashboard'])->name('dashboard'); 
    Route::post('/profile/upload', [CashierController::class, 'uploadProfilePicture'])->name('profile.upload');

    Route::get('/bet', [CashierController::class, 'bet'])->name('bet');
    Route::post('/bets/mark-paid', [CashierController::class, 'markPaid'])->name('bets.markPaid');
    Route::get('/remittance', [CashierController::class, 'remittance'])->name('remittance');
    Route::post('/approve-all/{agent}', [CashierController::class, 'approveAll'])->name('approveAll');

    Route::get('/collections', [CashierController::class, 'collections'])->name('collections');
    Route::get('/reports', [CashierController::class, 'reports'])->name('reports');
    Route::get('/cashier/report/export-pdf', [CashierController::class, 'exportCashierReportPdf'])->name('report.pdf');

    Route::get('/reports/remittance/pdf', [CashierController::class, 'exportRemittancePdf'])->name('reports.remittance.pdf');
    Route::get('/reports/gross/pdf', [CashierController::class, 'exportGrossPdf'])->name('reports.gross.pdf');
    Route::get('/reports/balances/pdf', [CashierController::class, 'exportBalancePdf'])->name('reports.balances.pdf');
    Route::get('/report/print-summary', [CashierController::class, 'printSummary'])->name('report.print');

    Route::get('/bet-history', [CashierController::class, 'betHistory'])->name('bet.history');
    Route::get('/winning-bets', [CashierController::class, 'winningBets'])->name('winning');
    Route::get('/results', [CashierController::class, 'results'])->name('results');
    Route::get('/support', [CashierController::class, 'support'])->name('support');
    Route::get('/settings', [CashierController::class, 'settings'])->name('settings');

    Route::get('/receipts', [CashierController::class, 'receipts'])->name('receipts.index');
    Route::get('/receipts/{stub}', [CashierController::class, 'showReceipt'])->name('receipts.show');
    Route::get('/receipts/{stub}/print', [CashierController::class, 'printReceipt'])->name('receipts.print');
    Route::get('/receipts/{stub}/export-pdf', [CashierController::class, 'singlePdf'])->name('receipts.singlePdf');
    Route::get('/receipt/stub/{stub}', [CashierController::class, 'printReceiptStub'])->name('receipt.printStub');
    Route::get('/receipts/agent/{agentId}/print', [CashierController::class, 'printReceiptsByAgent'])->name('receipts.printByAgent');
    Route::get('/receipts/export', [CashierController::class, 'exportPdf'])->name('receipts.export');
    Route::post('/receipts/export-pdf', [CashierController::class, 'fullPdf'])->name('receipts.fullPdf');
    Route::get('/remittance/{id}/receipt', [CashierController::class, 'showRemittanceReceipt'])->name('remittance.receipt');

    Route::get('/batches/{batch}/receipt', [CashierController::class, 'showBatchReceipt'])->name('batches.receipt');
    Route::get('/batches/{batch}/export-pdf', [CashierController::class, 'batchExport'])->name('receipts.batchExport');

    Route::get('/pending', [CashierController::class, 'pendingCollections'])->name('pending');
    Route::post('/collections/{id}/approve', [CashierController::class, 'approve'])->name('approve');
    Route::post('/collections/{id}/reject', [CashierController::class, 'reject'])->name('reject');
    Route::post('/generate-remittance/{agent}', [CashierController::class, 'generateRemittance'])->name('generateRemittance');
});

// Agent Routes
Route::middleware(['auth', 'role:agent'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/dashboard', [AgentController::class, 'index'])->name('dashboard');
    Route::get('/bet', [AgentController::class, 'bet'])->name('bet');
    Route::post('/bets/store', [BetController::class, 'store'])->name('bets.store'); 
    Route::get('/bet-history', [AgentController::class, 'betHistory'])->name('bet.history'); 
    Route::get('/winning-bets', [AgentController::class, 'winningBets'])->name('winning');
    Route::get('/results', [AgentController::class, 'results'])->name('results');
    Route::get('/reports', [AgentController::class, 'reports'])->name('reports');
    Route::get('/collections', [AgentController::class, 'collections'])->name('collections');
    Route::post('/collection/store', [AgentController::class, 'collect'])->name('collections.store');
    Route::get('/support', [AgentController::class, 'support'])->name('support');
    Route::get('/settings', [AgentController::class, 'settings'])->name('settings'); 

    Route::get('/profile', [AgentController::class, 'profile'])->name('profile');
    Route::post('/profile/upload', [AgentController::class, 'uploadProfilePicture'])->name('profile.upload');
    Route::get('/profile', [ProfileController::class, 'agentedit'])->name('agent-edit');
    Route::get('/receipt/summary/{stub?}', [AgentController::class, 'SummaryReceipt'])->name('receipt.summary');
    Route::get('/receipts/preview', [BetController::class, 'preview'])->name('receipts.preview');
    Route::get('/receipts/{stub}', [BetController::class, 'showReceipt'])->name('receipts.show');
    Route::get('/remit-preview', [AgentController::class, 'remitPreview'])->name('remit-preview');
    Route::get('/receipts/multi/{stub_ids}', [AgentController::class, 'multi'])->name('receipts.multi');
    
    Route::post('/receipts-json-multi', [BetController::class, 'showReceiptsJsonMulti'])->name('receipts.json.multi');
    Route::get('/multi-receipts', [BetController::class, 'showMultiReceipts'])->name('multi.receipts');

    Route::get('/receipts-json/{stub}', [BetController::class, 'getReceiptJson'])->name('receipts.json');












    Route::post('/check-hot-pick', [BetController::class, 'checkHotPick']);
    
    Route::get('/agent/printer-mac', function (Request $request) {
    $agent = $request->user();
    return response()->json([
        'mac' => $agent->printer_mac ?? null,
        'agentName' => $agent->name ?? 'Agent',
    ]);
    })->name('agent.printerMac');

});

// Utility Endpoint
Route::post('/sync-offline-bets', fn () => response()->json(['status' => 'success']));
Route::get('/api/check-hot-pick', function (Request $request) {
    $betNumber = $request->query('bet_number');
    $gameType = $request->query('game_type');
    $gameDraw = $request->query('game_draw');
    $gameDate = $request->query('game_date');

    $limits = [
        'L2' => 3000,
        'S3' => 1000,
        '4D' => 500
    ];

    $count = DB::table('bets')
        ->where('bet_number', $betNumber)
        ->where('game_type', $gameType)
        ->where('game_draw', $gameDraw)
        ->where('game_date', $gameDate)
        ->count();

    $limit = $limits[$gameType] ?? 999999;

    return response()->json([
        'locked' => $count >= $limit
    ]);
});
Route::get('/receipt/{stubId}', [BetController::class, 'getReceiptData']);

// Auth Routes (Fortify/Breeze)
require __DIR__ . '/auth.php';
