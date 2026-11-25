<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 20)->nullable()->unique();
            $table->foreignId('referred_by_user_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->index('referred_by_user_id');
        });

        DB::table('users')->select('id')->orderBy('id')->chunkById(200, function ($users) {
            foreach ($users as $user) {
                do {
                    $code = strtoupper(Str::random(10));
                } while (DB::table('users')->where('referral_code', $code)->exists());
                DB::table('users')->where('id', $user->id)->whereNull('referral_code')->update(['referral_code' => $code]);
            }
        });

        Schema::create('referral_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->decimal('first_deposit_percent', 7, 4)->default(10);
            $table->decimal('recurring_deposit_percent', 7, 4)->default(5);
            $table->decimal('minimum_deposit_usd', 20, 8)->default(0);
            $table->timestamps();
        });

        DB::table('referral_settings')->insert([
            'id' => 1, 'enabled' => true, 'first_deposit_percent' => 10,
            'recurring_deposit_percent' => 5, 'minimum_deposit_usd' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wallet_request_id')->unique()->constrained('wallet_requests')->cascadeOnDelete();
            $table->decimal('deposit_amount_usd', 20, 8);
            $table->decimal('commission_percent', 7, 4);
            $table->decimal('commission_amount_usd', 20, 8);
            $table->string('commission_type', 20);
            $table->string('status', 20)->default('pending')->index();
            $table->string('ledger_key', 120)->nullable()->unique();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->index(['referrer_user_id', 'status', 'created_at']);
            $table->index(['referred_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
        Schema::dropIfExists('referral_settings');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by_user_id']);
            $table->dropIndex(['referred_by_user_id']);
            $table->dropUnique(['referral_code']);
            $table->dropColumn(['referral_code', 'referred_by_user_id']);
        });
    }
};
