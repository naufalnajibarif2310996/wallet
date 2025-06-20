<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_balance_histories', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_address', 42)->index();
            $table->decimal('balance', 36, 18);
            $table->decimal('balance_usd', 15, 2)->default(0);
            $table->date('date');
            $table->string('network', 20)->default('ethereum');
            $table->timestamps();

            $table->unique(['wallet_address', 'date', 'network']);
            $table->foreign('wallet_address')->references('address')->on('wallets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_balance_histories');
    }
};