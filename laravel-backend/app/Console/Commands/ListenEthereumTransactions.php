<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\NewEthereumTransaction;

class ListenEthereumTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ethereum:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for Ethereum transactions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        while (true) {
            try {
                $wsUrl = 'wss://mainnet.infura.io/ws/v3/60b78f61d2bb4afbbbcdd22874eb627b';
                $client = new \WebSocket\Client($wsUrl);

                $client->send(json_encode([
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => 'eth_subscribe',
                    'params' => ['newHeads']
                ]));

                while (true) {
                    $message = $client->receive();
                    echo "Received: $message\n";
                    $data = json_decode($message, true);

                    // Hanya broadcast jika ada blok baru
                    if (isset($data['params']['result'])) {
                        event(new \App\Events\NewEthereumTransaction($data['params']['result']));
                    }
                }
            } catch (\WebSocket\ConnectionException $e) {
                echo "Timeout or connection error, reconnecting...\n";
                sleep(2); // Tunggu sebentar sebelum reconnect
            }
        }
    }
}
