<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {Schema::create('paper_conversions',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('request_key',100)->unique();$t->string('from_currency',12);$t->string('to_currency',12);$t->decimal('amount_in',24,8);$t->decimal('amount_out',24,8);$t->decimal('source_price',28,12);$t->decimal('target_price',28,12);$t->timestamp('source_quote_at')->nullable();$t->timestamp('target_quote_at')->nullable();$t->string('status',16)->default('completed');$t->timestamps();$t->index(['user_id','created_at']);});}
 public function down(): void {Schema::dropIfExists('paper_conversions');}
};