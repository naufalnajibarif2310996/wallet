<?php
$url = "https://mainnet.infura.io/v3/60b78f61d2bb4afbbbcdd22874eb627b";
$data = [
    "jsonrpc" => "2.0",
    "method" => "eth_blockNumber",
    "params" => [],
    "id" => 1
];
$options = [
    "http" => [
        "header"  => "Content-type: application/json\r\n",
        "method"  => "POST",
        "content" => json_encode($data),
        "timeout" => 10
    ]
];
$context  = stream_context_create($options);
$result = @file_get_contents($url, false, $context);
if ($result === FALSE) {
    echo "Koneksi ke Infura gagal.\n";
    if (isset($http_response_header)) {
        print_r($http_response_header);
    }
} else {
    echo "Berhasil konek ke Infura!\n";
    echo $result;
} 