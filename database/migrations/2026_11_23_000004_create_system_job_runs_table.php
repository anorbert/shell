<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {Schema::create('system_job_runs',function(Blueprint $t){$t->id();$t->string('job_key',80)->unique();$t->string('status',16)->default('unknown');$t->timestamp('last_started_at')->nullable();$t->timestamp('last_finished_at')->nullable();$t->timestamp('last_success_at')->nullable();$t->unsignedInteger('duration_ms')->nullable();$t->string('message',255)->nullable();$t->timestamps();});}
 public function down(): void {Schema::dropIfExists('system_job_runs');}
};