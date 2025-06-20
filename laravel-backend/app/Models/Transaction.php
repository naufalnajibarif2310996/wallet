<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_address',
        'hash',
        'type',
        'amount',
        'amount_usd',
        'to_address',
        'from_address',
        'block_number',
        'block_timestamp',
        'gas_used',
        'gas_price',
        'status',
        'network'
    ];

    protected $casts = [
        'amount' => 'decimal:18',
        'amount_usd' => 'decimal:2',
        'block_number' => 'integer',
        'block_timestamp' => 'datetime',
        'gas_used' => 'integer',
        'gas_price' => 'decimal:18'
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_address', 'address');
    }
}