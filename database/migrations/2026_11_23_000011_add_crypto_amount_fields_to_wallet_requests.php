<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_requests', function (Blueprint $table) {
            $table->decimal('asset_amount', 30, 12)->nullable()->after('amount_usd');
            $table->decimal('settlement_amount', 30, 12)->nullable()->after('asset_amount');
            $table->decimal('fee_usd', 20, 8)->nullable()->after('settlement_amount');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_requests', function (Blueprint $table) {
            $table->dropColumn(['asset_amount', 'settlement_amount', 'fee_usd']);
        });
    }
};
