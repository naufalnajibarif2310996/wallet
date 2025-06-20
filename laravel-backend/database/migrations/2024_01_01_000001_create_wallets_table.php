<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('address', 42)->unique()->index();
            $table->string('network', 20)->default('ethereum');
            $table->decimal('balance', 36, 18)->default(0);
            $table->decimal('balance_usd', 15, 2)->default(0);
            $table->timestamp('last_updated')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['address', 'network']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};