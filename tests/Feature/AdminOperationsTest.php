<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('clients cannot access administration operations', function () {
    $client = User::factory()->create();

    $this->actingAs($client)->get('/admin')->assertForbidden();
    $this->actingAs($client)->get('/admin/operations')->assertForbidden();
});

test('administrators can inspect market and paper trade operations', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin', 'email_verified_at' => now()])->save();

    DB::table('market_quotes')->insert([
        'symbol' => 'BTCUSDT',
        'price' => '90000.000000000000',
        'provider' => 'Test provider',
        'captured_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)->get('/admin/operations')
        ->assertOk()
        ->assertSee('Trading operations')
        ->assertSee('BTCUSDT')
        ->assertSee('FRESH')
        ->assertSee('Background job health');

    $this->assertDatabaseHas('admin_audit_logs', [
        'actor_user_id' => $admin->id,
        'path' => 'admin/operations',
    ]);
});

test('paper settlement command records its execution heartbeat', function () {
    $this->artisan('shell:paper:settle')->assertSuccessful();

    $this->assertDatabaseHas('system_job_runs', [
        'job_key' => 'paper_trade_settlement',
        'status' => 'success',
    ]);
});
