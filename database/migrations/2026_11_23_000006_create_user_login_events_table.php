<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {Schema::create('user_login_events',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('event_type',16);$t->string('ip_address',45)->nullable();$t->text('user_agent')->nullable();$t->timestamp('created_at')->useCurrent();$t->index(['user_id','created_at']);});}
 public function down(): void {Schema::dropIfExists('user_login_events');}
};