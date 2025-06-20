<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Services\Web3Service;

class WalletController extends Controller
{
    private $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get wallet information
     * GET /api/wallet/{address}
     */
    public function show(Request $request, $address): JsonResponse
    {
        try {
            $network = $request->get('network', 'ethereum');
            
            // Validate network
            $allowedNetworks = ['ethereum', 'bsc', 'polygon'];
            if (!in_array($network, $allowedNetworks)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid network. Allowed networks: ' . implode(', ', $allowedNetworks)
                ], 400);
            }

            $walletInfo = $this->walletService->getWalletInfo($address, $network);

            return response()->json([
                'success' => true,
                'data' => $walletInfo
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update wallet data
     * POST /api/wallet/{address}/refresh
     */
    public function refresh(Request $request, $address): JsonResponse
    {
        try {
            $network = $request->get('network', 'ethereum');
            
            $wallet = $this->walletService->getOrCreateWallet($address, $network);
            $updatedWallet = $this->walletService->updateWalletData($wallet, $network);
            
            $walletInfo = $this->walletService->getWalletInfo($address, $network);

            return response()->json([
                'success' => true,
                'message' => 'Wallet data updated successfully',
                'data' => $walletInfo
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet statistics
     * GET /api/wallet/{address}/stats
     */
    public function stats(Request $request, $address): JsonResponse
    {
        try {
            $network = $request->get('network', 'ethereum');
            $stats = $this->walletService->getWalletStats($address, $network);

            if (!$stats) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log wallet view
     * POST /api/wallet/log
     */
    public function logView(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'address' => 'required|string|regex:/^0x[a-fA-F0-9]{40}$/',
                'network' => 'sometimes|string|in:ethereum,bsc,polygon'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $address = $request->input('address');
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();

            $this->walletService->logWalletView($address, $ipAddress, $userAgent);

            return response()->json([
                'success' => true,
                'message' => 'Wallet view logged successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get supported networks
     * GET /api/networks
     */
    public function networks(): JsonResponse
    {
        $networks = [
            [
                'id' => 'ethereum',
                'name' => 'Ethereum Mainnet',
                'chain_id' => 1,
                'symbol' => 'ETH',
                'explorer' => 'https://etherscan.io',
                'rpc_url' => config('services.ethereum.rpc_url')
            ],
            [
                'id' => 'bsc',
                'name' => 'Binance Smart Chain',
                'chain_id' => 56,
                'symbol' => 'BNB',
                'explorer' => 'https://bscscan.com',
                'rpc_url' => config('services.bsc.rpc_url')
            ],
            [
                'id' => 'polygon',
                'name' => 'Polygon',
                'chain_id' => 137,
                'symbol' => 'MATIC',
                'explorer' => 'https://polygonscan.com',
                'rpc_url' => config('services.polygon.rpc_url')
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $networks
        ]);
    }

    public function getWalletBalance(Request $request)
    {
        $address = $request->input('address');
        $balance = app(Web3Service::class)->getBalance($address);
        return response()->json(['balance' => $balance]);
    }
}