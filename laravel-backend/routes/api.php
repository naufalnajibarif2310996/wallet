<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\Web3LoginController;

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

Route::post('/login/web3', [Web3LoginController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Wallet API Routes
Route::prefix('wallet')->group(function () {
    // Get wallet information
    Route::get('/{address}', [WalletController::class, 'show']);
    
    // Refresh wallet data
    Route::post('/{address}/refresh', [WalletController::class, 'refresh']);
    
    // Get wallet statistics
    Route::get('/{address}/stats', [WalletController::class, 'stats']);
    
    // Log wallet view
    Route::post('/log', [WalletController::class, 'logView']);
});

// Get supported networks
Route::get('/networks', [WalletController::class, 'networks']);

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});