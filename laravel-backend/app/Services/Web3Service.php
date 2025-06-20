<?php

namespace App\Services;

use Web3\Web3;
use Web3\Contract;
use Web3\Utils;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Web3\Personal;
use kornrunner\Keccak;
use GuzzleHttp\Client;

class Web3Service
{
    private $web3;
    private $etherscanApiKey;
    private $bscscanApiKey;
    private $moralisApiKey;

    public function __construct()
    {
        $this->etherscanApiKey = config('services.etherscan.api_key');
        $this->bscscanApiKey = config('services.bscscan.api_key');
        $this->moralisApiKey = config('services.moralis.api_key');
    }

    public function getWeb3Instance($network = 'ethereum')
    {
        $rpcUrl = match($network) {
            'ethereum' => config('services.ethereum.rpc_url'),
            'bsc' => config('services.bsc.rpc_url'),
            'polygon' => config('services.polygon.rpc_url'),
            default => config('services.ethereum.rpc_url')
        };

        return new Web3($rpcUrl);
    }

    public function getBalance($address, $network = 'ethereum')
    {
        try {
            $rpcUrl = match($network) {
                'ethereum' => config('services.ethereum.rpc_url'),
                'bsc' => config('services.bsc.rpc_url'),
                'polygon' => config('services.polygon.rpc_url'),
                default => config('services.ethereum.rpc_url')
            };
            $client = new Client();

            $payload = [
                'jsonrpc' => '2.0',
                'method'  => 'eth_getBalance',
                'params'  => [$address, 'latest'],
                'id'      => 1,
            ];

            $response = $client->post($rpcUrl, [
                'json' => $payload,
                'timeout' => 10
            ]);

            $result = json_decode($response->getBody(), true);
            \Log::info('Infura balance response: ' . json_encode($result));

            if (isset($result['result']) && is_string($result['result']) && strpos($result['result'], '0x') === 0) {
                $balanceHex = $result['result'];
                // Konversi hex ke desimal
                $balance = hexdec($balanceHex);
                // Konversi Wei ke Ether
                $balanceEther = $balance / pow(10, 18);
                return $balanceEther;
            } else {
                throw new Exception('Invalid balance result from Infura: ' . json_encode($result));
            }
        } catch (Exception $e) {
            \Log::error('Error getBalance: ' . $e->getMessage());
            return null;
        }
    }

    public function getTransactions($address, $network = 'ethereum', $limit = 10)
    {
        try {
            $apiKey = $network === 'bsc' ? $this->bscscanApiKey : $this->etherscanApiKey;
            $baseUrl = $network === 'bsc' 
                ? 'https://api.bscscan.com/api' 
                : 'https://api.etherscan.io/api';

            $response = Http::get($baseUrl, [
                'module' => 'account',
                'action' => 'txlist',
                'address' => $address,
                'startblock' => 0,
                'endblock' => 99999999,
                'page' => 1,
                'offset' => $limit,
                'sort' => 'desc',
                'apikey' => $apiKey
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === '1') {
                    return $this->formatTransactions($data['result'], $address, $network);
                }
            }

            throw new Exception('Failed to fetch transactions from blockchain explorer');
        } catch (Exception $e) {
            Log::error('Error getting transactions for address ' . $address . ': ' . $e->getMessage());
            throw $e;
        }
    }

    public function getTokenPrice($symbol = 'ethereum')
    {
        try {
            $response = Http::get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => $symbol,
                'vs_currencies' => 'usd'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data[$symbol]['usd'] ?? 0;
            }

            return 0;
        } catch (Exception $e) {
            Log::error('Error getting token price: ' . $e->getMessage());
            return 0;
        }
    }

    public function getTokenBalance($address, $tokenContract, $network = 'ethereum')
    {
        try {
            $web3 = $this->getWeb3Instance($network);
            
            // ERC-20 balanceOf function signature
            $balanceOfAbi = [
                [
                    'constant' => true,
                    'inputs' => [['name' => '_owner', 'type' => 'address']],
                    'name' => 'balanceOf',
                    'outputs' => [['name' => 'balance', 'type' => 'uint256']],
                    'type' => 'function'
                ]
            ];

            $contract = new Contract($web3->provider, $balanceOfAbi);
            $contractInstance = $contract->at($tokenContract);
            
            $balance = null;
            $contractInstance->call('balanceOf', $address, function ($err, $result) use (&$balance) {
                if ($err !== null) {
                    throw new Exception('Error getting token balance: ' . $err->getMessage());
                }
                $balance = $result[0];
            });

            return $balance;
        } catch (Exception $e) {
            Log::error('Error getting token balance: ' . $e->getMessage());
            throw $e;
        }
    }

    private function formatTransactions($transactions, $walletAddress, $network)
    {
        $formatted = [];
        $ethPrice = $this->getTokenPrice('ethereum');

        foreach ($transactions as $tx) {
            $amount = Utils::fromWei($tx['value'], 'ether');
            $type = strtolower($tx['to']) === strtolower($walletAddress) ? 'received' : 'sent';
            
            $formatted[] = [
                'hash' => $tx['hash'],
                'type' => $type,
                'amount' => $amount,
                'amount_usd' => floatval($amount) * $ethPrice,
                'to_address' => $tx['to'],
                'from_address' => $tx['from'],
                'block_number' => intval($tx['blockNumber']),
                'block_timestamp' => date('Y-m-d H:i:s', $tx['timeStamp']),
                'gas_used' => intval($tx['gasUsed']),
                'gas_price' => Utils::fromWei($tx['gasPrice'], 'gwei'),
                'status' => $tx['txreceipt_status'] === '1' ? 'confirmed' : 'failed',
                'network' => $network
            ];
        }

        return $formatted;
    }

    public function verifySignature(string $message, string $signature, string $address): bool
    {
        try {
            $personal = new Personal($this->getWeb3Instance()->provider);
            $recoveredAddress = '';

            // Prefix message as per EIP-191
            $prefix = "\x19Ethereum Signed Message:\n" . strlen($message);
            $messageHash = Keccak::hash($prefix . $message, 256);

            $personal->ecRecover($messageHash, $signature, function ($err, $result) use (&$recoveredAddress) {
                if ($err !== null) {
                    throw new Exception('Error recovering address: ' . $err->getMessage());
                }
                $recoveredAddress = $result;
            });

            return strtolower($recoveredAddress) === strtolower($address);
        } catch (Exception $e) {
            Log::error('Error verifying signature for address ' . $address . ': ' . $e->getMessage());
            return false;
        }
    }

    public function isValidAddress($address)
    {
        return preg_match('/^0x[a-fA-F0-9]{40}$/', $address);
    }

    public function getNetworkInfo($network = 'ethereum')
    {
        $networks = [
            'ethereum' => [
                'name' => 'Ethereum Mainnet',
                'chain_id' => 1,
                'symbol' => 'ETH',
                'explorer' => 'https://etherscan.io'
            ],
            'bsc' => [
                'name' => 'Binance Smart Chain',
                'chain_id' => 56,
                'symbol' => 'BNB',
                'explorer' => 'https://bscscan.com'
            ],
            'polygon' => [
                'name' => 'Polygon',
                'chain_id' => 137,
                'symbol' => 'MATIC',
                'explorer' => 'https://polygonscan.com'
            ]
        ];

        return $networks[$network] ?? $networks['ethereum'];
    }
}