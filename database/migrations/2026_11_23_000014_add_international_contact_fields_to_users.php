<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('country_code', 2)->nullable()->after('email');
            $table->string('phone_e164', 16)->nullable()->after('country_code');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->index('country_code');
            $table->index('phone_e164');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['country_code']);
            $table->dropIndex(['phone_e164']);
            $table->dropColumn(['country_code', 'phone_e164', 'phone_verified_at']);
        });
    }
};
