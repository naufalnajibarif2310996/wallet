<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Web3Service;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Web3LoginController extends Controller
{
    protected $web3Service;

    public function __construct(Web3Service $web3Service)
    {
        $this->web3Service = $web3Service;
    }

    public function login(Request $request)
    {
        $request->validate([
            'address' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'signature' => ['required', 'string'],
            'message' => ['required', 'string'],
        ]);

        $address = $request->input('address');
        $signature = $request->input('signature');
        $message = $request->input('message');

        if (!$this->web3Service->verifySignature($message, $signature, $address)) {
            return response()->json(['error' => 'Invalid signature.'], 401);
        }

        $user = User::firstOrCreate(
            ['wallet_address' => $address],
            ['name' => 'user_'.substr($address, 2, 8)] // Default name
        );

        // Revoke any existing tokens
        $user->tokens()->delete();

        // Create a new token
        $token = $user->createToken('web3-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }
} 