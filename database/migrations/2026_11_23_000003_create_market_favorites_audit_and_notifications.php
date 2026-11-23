<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('market_favorites', function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('symbol',24);$t->timestamps();$t->unique(['user_id','symbol']);});
  Schema::create('admin_audit_logs', function(Blueprint $t){$t->id();$t->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();$t->string('action',80);$t->string('method',10);$t->string('path',255);$t->string('ip_address',45)->nullable();$t->text('user_agent')->nullable();$t->timestamp('created_at')->useCurrent();$t->index(['actor_user_id','created_at']);$t->index('created_at');});
  Schema::create('user_notifications', function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('event_key',120)->unique();$t->string('type',40);$t->string('title',140);$t->text('body');$t->json('data')->nullable();$t->timestamp('read_at')->nullable();$t->timestamps();$t->index(['user_id','read_at','created_at']);});
 }
 public function down(): void {Schema::dropIfExists('user_notifications');Schema::dropIfExists('admin_audit_logs');Schema::dropIfExists('market_favorites');}
};