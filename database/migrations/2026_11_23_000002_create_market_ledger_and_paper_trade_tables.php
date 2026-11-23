<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep this migration safe to rerun after MySQL has committed earlier
        // CREATE TABLE statements but failed on a later table.
        if (! Schema::hasTable('market_quotes')) {
            Schema::create('market_quotes', function (Blueprint $table): void {
                $table->id();
                $table->string('symbol', 24)->unique();
                $table->decimal('price', 28, 12);
                $table->string('provider', 40);
                $table->dateTime('captured_at');
                $table->timestamps();
                $table->index(['symbol', 'captured_at']);
            });
        }

        if (! Schema::hasTable('ledger_accounts')) {
            Schema::create('ledger_accounts', function (Blueprint $table): void {
                $table->id();
                $table->string('owner_key', 80);
                $table->string('code', 40);
                $table->string('currency', 12);
                $table->string('normal_side', 6);
                $table->timestamps();
                $table->unique(
                    ['owner_key', 'code', 'currency'],
                    'ledger_account_identity_unique'
                );
            });
        }

        if (! Schema::hasTable('ledger_transactions')) {
            Schema::create('ledger_transactions', function (Blueprint $table): void {
                $table->id();
                $table->string('idempotency_key', 120)->unique();
                $table->string('description', 180);
                $table->json('metadata')->nullable();
                $table->dateTime('posted_at');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ledger_entries')) {
            Schema::create('ledger_entries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('ledger_transaction_id')->constrained()->restrictOnDelete();
                $table->foreignId('ledger_account_id')->constrained()->restrictOnDelete();
                $table->string('side', 6);
                $table->decimal('amount', 24, 8);
                $table->timestamps();
                $table->index(['ledger_account_id', 'ledger_transaction_id']);
            });
        }

        if (! Schema::hasTable('paper_trades')) {
            Schema::create('paper_trades', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('symbol', 24);
                $table->string('direction', 4);
                $table->decimal('stake', 24, 8);
                $table->decimal('opening_price', 28, 12);
                $table->decimal('closing_price', 28, 12)->nullable();
                $table->unsignedInteger('duration_seconds');
                $table->string('status', 16)->default('open');
                $table->string('open_idempotency_key', 120)->unique();
                $table->string('settlement_idempotency_key', 120)->nullable()->unique();

                // DATETIME avoids legacy MySQL TIMESTAMP default restrictions.
                // Expiry is intentionally NOT nullable and has no default:
                // every trade must receive its actual expiry from the trade service.
                $table->dateTime('opened_at');
                $table->dateTime('expires_at');
                $table->dateTime('settled_at')->nullable();

                $table->timestamps();
                $table->index(['status', 'expires_at']);
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_trades');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('ledger_accounts');
        Schema::dropIfExists('market_quotes');
    }
};
