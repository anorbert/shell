<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('guest_name', 100)->nullable()->after('user_id');
            $table->string('guest_email', 254)->nullable()->after('guest_name');
            $table->string('guest_access_token_hash', 64)->nullable()->after('guest_email');
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->index('guest_email');
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex(['guest_email']);
            $table->dropColumn(['guest_name', 'guest_email', 'guest_access_token_hash']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
