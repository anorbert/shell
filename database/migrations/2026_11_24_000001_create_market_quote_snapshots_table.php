<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_quote_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 24);
            $table->decimal('price', 28, 12);
            $table->timestamp('captured_at');
            $table->index(['symbol', 'captured_at'], 'mqs_symbol_captured_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_quote_snapshots');
    }
};
