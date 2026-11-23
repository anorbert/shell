<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('support_tickets',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('subject',140);$t->string('category',32);$t->string('status',16)->default('open');$t->timestamp('last_message_at')->nullable();$t->timestamp('closed_at')->nullable();$t->timestamps();$t->index(['status','last_message_at']);$t->index(['user_id','created_at']);});
  Schema::create('support_messages',function(Blueprint $t){$t->id();$t->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('sender_role',12);$t->text('message');$t->timestamps();$t->index(['support_ticket_id','created_at']);});
 }
 public function down(): void {Schema::dropIfExists('support_messages');Schema::dropIfExists('support_tickets');}
};