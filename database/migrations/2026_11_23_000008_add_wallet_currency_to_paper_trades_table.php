<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paper_trades', function (Blueprint $table) {
            // Existing trades were opened against the previous USDT demo wallet.
            // New trades explicitly record USD after the dual-wallet rollout.
            $table->string('wallet_currency', 12)->default('USDT')->after('stake');
        });
    }

    public function down(): void
    {
        Schema::table('paper_trades', function (Blueprint $table) {
            $table->dropColumn('wallet_currency');
        });
    }
};
