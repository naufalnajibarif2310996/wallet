<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Web3 Services Configuration
    'ethereum' => [
        'rpc_url' => env('ETHEREUM_RPC_URL'),
    ],

    'bsc' => [
        'rpc_url' => env('BSC_RPC_URL', 'https://bsc-dataseed.binance.org/'),
    ],

    'polygon' => [
        'rpc_url' => env('POLYGON_RPC_URL', 'https://polygon-rpc.com/'),
    ],

    'etherscan' => [
        'api_key' => env('ETHERSCAN_API_KEY'),
    ],

    'bscscan' => [
        'api_key' => env('BSCSCAN_API_KEY'),
    ],

    'moralis' => [
        'api_key' => env('MORALIS_API_KEY'),
        'api_url' => env('MORALIS_API_URL', 'https://deep-index.moralis.io/api/v2'),
    ],

    'coingecko' => [
        'api_url' => env('COINGECKO_API_URL', 'https://api.coingecko.com/api/v3'),
    ],

];