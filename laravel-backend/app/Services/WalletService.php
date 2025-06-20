<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\WalletBalanceHistory;
use App\Models\WalletViewLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class WalletService
{
    private $web3Service;

    public function __construct(Web3Service $web3Service)
    {
        $this->web3Service = $web3Service;
    }

    public function getWalletInfo($address, $network = 'ethereum')
    {
        try {
            if (!$this->web3Service->isValidAddress($address)) {
                throw new Exception('Invalid wallet address format');
            }

            // Get or create wallet record
            $wallet = $this->getOrCreateWallet($address, $network);

            // Update wallet data if it's stale (older than 5 minutes)
            if (!$wallet->last_updated || $wallet->last_updated->diffInMinutes(now()) > 5) {
                $this->updateWalletData($wallet, $network);
            }

            // Get recent transactions
            $transactions = $this->getRecentTransactions($address, $network);

            // Get balance history
            $balanceHistory = $this->getBalanceHistory($address, $network);

            $networkInfo = $this->web3Service->getNetworkInfo($network);

            return [
                'address' => $wallet->address,
                'balance' => $wallet->balance,
                'balance_usd' => $wallet->balance_usd,
                'network' => $networkInfo['name'],
                'last_updated' => $wallet->last_updated,
                'transactions' => $transactions,
                'balance_history' => $balanceHistory
            ];
        } catch (Exception $e) {
            Log::error('Error getting wallet info: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateWalletData($wallet, $network = 'ethereum')
    {
        try {
            // Get current balance
            $balance = $this->web3Service->getBalance($wallet->address, $network);
            
            // Get token price
            $tokenPrice = $this->web3Service->getTokenPrice($network === 'bsc' ? 'binancecoin' : 'ethereum');
            $balanceUsd = floatval($balance) * $tokenPrice;

            // Update wallet
            $wallet->update([
                'balance' => $balance,
                'balance_usd' => $balanceUsd,
                'last_updated' => now(),
                'is_active' => true
            ]);

            // Store balance history
            $this->storeBalanceHistory($wallet->address, $balance, $balanceUsd, $network);

            // Update transactions
            $this->updateTransactions($wallet->address, $network);

            return $wallet->fresh();
        } catch (Exception $e) {
            Log::error('Error updating wallet data: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getOrCreateWallet($address, $network = 'ethereum')
    {
        return Wallet::firstOrCreate(
            ['address' => $address],
            [
                'network' => $network,
                'balance' => '0',
                'balance_usd' => 0,
                'is_active' => true
            ]
        );
    }

    public function updateTransactions($address, $network = 'ethereum')
    {
        try {
            $transactions = $this->web3Service->getTransactions($address, $network, 20);

            foreach ($transactions as $txData) {
                Transaction::updateOrCreate(
                    [
                        'hash' => $txData['hash'],
                        'wallet_address' => $address
                    ],
                    $txData + ['wallet_address' => $address]
                );
            }
        } catch (Exception $e) {
            Log::error('Error updating transactions: ' . $e->getMessage());
        }
    }

    public function getRecentTransactions($address, $network = 'ethereum', $limit = 5)
    {
        return Transaction::where('wallet_address', $address)
            ->where('network', $network)
            ->orderBy('block_timestamp', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'hash' => $tx->hash,
                    'type' => $tx->type,
                    'amount' => $tx->amount,
                    'amount_usd' => $tx->amount_usd,
                    'to' => $tx->to_address,
                    'from' => $tx->from_address,
                    'timestamp' => $tx->block_timestamp->toISOString(),
                    'status' => $tx->status,
                    'gas_used' => $tx->gas_used,
                    'gas_price' => $tx->gas_price
                ];
            });
    }

    public function storeBalanceHistory($address, $balance, $balanceUsd, $network = 'ethereum')
    {
        $today = Carbon::today();

        WalletBalanceHistory::updateOrCreate(
            [
                'wallet_address' => $address,
                'date' => $today,
                'network' => $network
            ],
            [
                'balance' => $balance,
                'balance_usd' => $balanceUsd
            ]
        );
    }

    public function getBalanceHistory($address, $network = 'ethereum', $days = 30)
    {
        $history = WalletBalanceHistory::where('wallet_address', $address)
            ->where('network', $network)
            ->where('date', '>=', Carbon::now()->subDays($days))
            ->orderBy('date')
            ->get();

        // Fill missing dates with previous balance
        $result = [];
        $lastBalance = 0;
        $lastBalanceUsd = 0;

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $record = $history->firstWhere('date', $date);

            if ($record) {
                $lastBalance = $record->balance;
                $lastBalanceUsd = $record->balance_usd;
            }

            $result[] = [
                'date' => $date,
                'balance' => floatval($lastBalance),
                'balance_usd' => floatval($lastBalanceUsd)
            ];
        }

        return $result;
    }

    public function logWalletView($address, $ipAddress, $userAgent)
    {
        try {
            WalletViewLog::create([
                'wallet_address' => $address,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'viewed_at' => now()
            ]);
        } catch (Exception $e) {
            Log::error('Error logging wallet view: ' . $e->getMessage());
        }
    }

    public function getWalletStats($address, $network = 'ethereum')
    {
        $wallet = Wallet::where('address', $address)->first();
        
        if (!$wallet) {
            return null;
        }

        $totalTransactions = Transaction::where('wallet_address', $address)
            ->where('network', $network)
            ->count();

        $totalReceived = Transaction::where('wallet_address', $address)
            ->where('network', $network)
            ->where('type', 'received')
            ->sum('amount');

        $totalSent = Transaction::where('wallet_address', $address)
            ->where('network', $network)
            ->where('type', 'sent')
            ->sum('amount');

        $viewCount = WalletViewLog::where('wallet_address', $address)->count();

        return [
            'total_transactions' => $totalTransactions,
            'total_received' => $totalReceived,
            'total_sent' => $totalSent,
            'view_count' => $viewCount,
            'first_transaction' => Transaction::where('wallet_address', $address)
                ->where('network', $network)
                ->orderBy('block_timestamp')
                ->first()?->block_timestamp,
            'last_transaction' => Transaction::where('wallet_address', $address)
                ->where('network', $network)
                ->orderBy('block_timestamp', 'desc')
                ->first()?->block_timestamp
        ];
    }
}