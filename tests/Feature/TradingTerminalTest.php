<?php

use App\Models\User;

test('trading terminal presents USD demo stakes and payout estimate', function () {
    $client = User::factory()->create();
    $client->markEmailAsVerified();

    $this->actingAs($client)
        ->get('/trading')
        ->assertOk()
        ->assertSee('Stake (USD)')
        ->assertSee('AVAILABLE DEMO BALANCE')
        ->assertSee('Potential total return')
        ->assertDontSee('Stake (USDT)');
});

test('conversion workspace defaults to the funded USD demo wallet', function () {
    $client = User::factory()->create();
    $client->markEmailAsVerified();

    $this->actingAs($client)
        ->get('/convert')
        ->assertOk()
        ->assertSee('USD · available 20000.00000000')
        ->assertSee('Available balance: 20000.00000000 USD');
});
