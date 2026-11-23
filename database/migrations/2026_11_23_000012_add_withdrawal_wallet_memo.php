<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_requests', function (Blueprint $table) {
            $table->string('wallet_memo', 255)->nullable()->after('wallet_address');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_requests', function (Blueprint $table) {
            $table->dropColumn('wallet_memo');
        });
    }
};
