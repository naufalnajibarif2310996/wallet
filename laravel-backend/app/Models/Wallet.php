<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'network',
        'balance',
        'balance_usd',
        'last_updated',
        'is_active'
    ];

    protected $casts = [
        'balance' => 'decimal:18',
        'balance_usd' => 'decimal:2',
        'last_updated' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'wallet_address', 'address');
    }

    public function balanceHistory(): HasMany
    {
        return $this->hasMany(WalletBalanceHistory::class, 'wallet_address', 'address');
    }

    public function viewLogs(): HasMany
    {
        return $this->hasMany(WalletViewLog::class, 'wallet_address', 'address');
    }
}