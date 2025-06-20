<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_address', 42)->index();
            $table->string('hash', 66)->unique();
            $table->enum('type', ['sent', 'received']);
            $table->decimal('amount', 36, 18);
            $table->decimal('amount_usd', 15, 2)->default(0);
            $table->string('to_address', 42);
            $table->string('from_address', 42);
            $table->bigInteger('block_number');
            $table->timestamp('block_timestamp');
            $table->bigInteger('gas_used')->nullable();
            $table->decimal('gas_price', 36, 18)->nullable();
            $table->enum('status', ['confirmed', 'pending', 'failed'])->default('confirmed');
            $table->string('network', 20)->default('ethereum');
            $table->timestamps();

            $table->index(['wallet_address', 'block_timestamp']);
            $table->index(['wallet_address', 'network']);
            $table->foreign('wallet_address')->references('address')->on('wallets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};