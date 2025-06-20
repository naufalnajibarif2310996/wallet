<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletBalanceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_address',
        'balance',
        'balance_usd',
        'date',
        'network'
    ];

    protected $casts = [
        'balance' => 'decimal:18',
        'balance_usd' => 'decimal:2',
        'date' => 'date'
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_address', 'address');
    }
}