<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('verified clients can request candles for configured market symbols', function () {
    $client = User::factory()->create();
    $client->markEmailAsVerified();

    Http::fake([
        'api.binance.com/api/v3/klines*' => Http::response([
            [1730000000000, '90000.00', '90500.00', '89900.00', '90300.00', '12.50', 1730000059999],
        ], 200),
    ]);

    $this->actingAs($client)
        ->getJson('/markets/candles?symbol=BTCUSDT&interval=1m&limit=20')
        ->assertOk()
        ->assertJsonPath('provider', 'Binance Spot')
        ->assertJsonPath('candles.0.open', '90000.00')
        ->assertJsonPath('candles.0.close', '90300.00');
});

test('candle endpoint rejects unsupported market symbols', function () {
    $client = User::factory()->create();
    $client->markEmailAsVerified();

    $this->actingAs($client)
        ->getJson('/markets/candles?symbol=NOTREAL&interval=1m')
        ->assertUnprocessable()
        ->assertJsonFragment(['message' => 'Unsupported market pair.']);
});

test('guests cannot access candle data', function () {
    $this->getJson('/markets/candles?symbol=BTCUSDT&interval=1m')->assertUnauthorized();
});
