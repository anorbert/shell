<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 40);
            $table->string('document_number', 120)->nullable();
            $table->string('document_path');
            $table->string('selfie_path')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('crypto_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('asset', 20);
            $table->string('network', 40);
            $table->string('wallet_address', 255);
            $table->string('memo_label', 80)->nullable();
            $table->string('memo_value', 255)->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('deposits_enabled')->default(true);
            $table->boolean('withdrawals_enabled')->default(true);
            $table->timestamps();
            $table->unique(['asset', 'network']);
        });

        Schema::create('wallet_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->index();
            $table->string('status', 20)->default('pending')->index();
            $table->decimal('amount_usd', 20, 8);
            $table->string('asset', 20);
            $table->string('network', 40);
            $table->string('wallet_address', 255)->nullable();
            $table->string('transaction_hash', 255)->nullable();
            $table->string('client_reference', 100)->nullable();
            $table->text('client_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('ledger_key', 120)->nullable()->unique();
            $table->timestamps();
            $table->index(['type', 'status', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->unique(['transaction_hash', 'network'], 'wallet_req_tx_network_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_requests');
        Schema::dropIfExists('crypto_payment_methods');
        Schema::dropIfExists('kyc_submissions');
    }
};
