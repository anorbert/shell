<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'demo_trading_mode')) {
                $table->string('demo_trading_mode', 16)->default('normal');
            }
            if (! Schema::hasColumn('users', 'real_trading_mode')) {
                $table->string('real_trading_mode', 16)->default('normal');
            }
        });

        Schema::table('paper_trades', function (Blueprint $table): void {
            if (! Schema::hasColumn('paper_trades', 'demo_outcome_mode')) {
                $table->string('demo_outcome_mode', 16)->default('normal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('paper_trades', function (Blueprint $table): void {
            if (Schema::hasColumn('paper_trades', 'demo_outcome_mode')) {
                $table->dropColumn('demo_outcome_mode');
            }
        });
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'real_trading_mode')) {
                $table->dropColumn('real_trading_mode');
            }
            if (Schema::hasColumn('users', 'demo_trading_mode')) {
                $table->dropColumn('demo_trading_mode');
            }
        });
    }
};
