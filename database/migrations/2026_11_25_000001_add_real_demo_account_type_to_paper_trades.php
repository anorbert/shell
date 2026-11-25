<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('paper_trades', 'account_type')) {
            Schema::table('paper_trades', function (Blueprint $table): void {
                // Existing rows are historical demo positions. New real positions
                // explicitly persist their account so switching accounts cannot
                // redirect an open position's stake or settlement.
                $table->string('account_type', 8)->default('demo')->after('user_id');
                $table->index(['user_id', 'account_type', 'status'], 'paper_trades_user_account_status_idx');
            });
        }

        if (! Schema::hasColumn('paper_trades', 'payout_basis_points')) {
            Schema::table('paper_trades', function (Blueprint $table): void {
                // Snapshot the return rate at opening; configuration changes must
                // never rewrite the payout terms of an already-open position.
                $table->unsignedInteger('payout_basis_points')->default(18000)->after('wallet_currency');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('paper_trades', 'payout_basis_points')) {
            Schema::table('paper_trades', function (Blueprint $table): void {
                $table->dropColumn('payout_basis_points');
            });
        }

        if (Schema::hasColumn('paper_trades', 'account_type')) {
            Schema::table('paper_trades', function (Blueprint $table): void {
                $table->dropIndex('paper_trades_user_account_status_idx');
                $table->dropColumn('account_type');
            });
        }
    }
};
