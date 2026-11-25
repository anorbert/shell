<?php
use App\Models\User;
use App\Services\LedgerService;
use App\Services\PaperConversionService;
use Illuminate\Support\Facades\DB;

test('paper conversions move balances through balanced currency ledgers and are idempotent', function () {
    $user = User::factory()->create();
    $ledger = app(LedgerService::class);
    $ledger->creditPaperWallet($user, '10.00000000', 'USDT');

    $capturedAt = now()->startOfSecond();
    DB::table('market_quotes')->insert([
        'symbol' => 'BTCUSDT',
        'price' => '50000.000000000000',
        'provider' => 'Test provider',
        'captured_at' => $capturedAt,
        'created_at' => $capturedAt,
        'updated_at' => $capturedAt,
    ]);

    $service = app(PaperConversionService::class);
    $quote = $service->quote('USDT', 'BTC', '10.00000000');
    expect($quote['amount_out'])->toBe('0.00020000');

    $conversion = $service->convert(
        $user,
        'USDT',
        'BTC',
        '10.00000000',
        '',
        $quote['target_quote_at'],
        'conversion-test-key'
    );

    expect($conversion->status)->toBe('completed')
        ->and((float) $conversion->amount_out)->toBe(0.0002)
        ->and($ledger->balanceUnits($ledger->wallet($user, 'USDT')))->toBe(0)
        ->and($ledger->balanceUnits($ledger->wallet($user, 'BTC')))->toBe(20000);

    $again = $service->convert(
        $user,
        'USDT',
        'BTC',
        '10.00000000',
        '',
        $quote['target_quote_at'],
        'conversion-test-key'
    );

    expect($again->id)->toBe($conversion->id)
        ->and(DB::table('paper_conversions')->where('request_key', 'conversion-test-key')->count())->toBe(1);
});

test('paper conversion rejects a changed market quote and leaves balances untouched', function () {
    $user = User::factory()->create();
    $ledger = app(LedgerService::class);
    $ledger->creditPaperWallet($user, '10.00000000', 'USDT');

    DB::table('market_quotes')->insert([
        'symbol' => 'BTCUSDT',
        'price' => '50000.000000000000',
        'provider' => 'Test provider',
        'captured_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = app(PaperConversionService::class);
    $quote = $service->quote('USDT', 'BTC', '10.00000000');
    DB::table('market_quotes')->where('symbol', 'BTCUSDT')->update([
        'price' => '51000.000000000000',
        // A changed but valid server timestamp; future-dated quotes are rejected.
        'captured_at' => now()->subSecond(),
        'updated_at' => now()->subSecond(),
    ]);

    expect(fn () => $service->convert(
        $user,
        'USDT',
        'BTC',
        '10.00000000',
        '',
        $quote['target_quote_at'],
        'stale-conversion-key'
    ))->toThrow(RuntimeException::class, 'Market quote changed.');

    expect($ledger->balanceUnits($ledger->wallet($user, 'USDT')))->toBe(1000000000)
        ->and(DB::table('paper_conversions')->where('request_key', 'stale-conversion-key')->exists())->toBeFalse();
});

test('paper conversion idempotency key cannot be reused with different inputs', function () {
    $user = User::factory()->create();
    app(LedgerService::class)->creditPaperWallet($user, '20.00000000', 'USDT');
    $capturedAt = now()->startOfSecond();
    DB::table('market_quotes')->insert([
        'symbol' => 'BTCUSDT',
        'price' => '50000.000000000000',
        'provider' => 'Test provider',
        'captured_at' => $capturedAt,
        'created_at' => $capturedAt,
        'updated_at' => $capturedAt,
    ]);
    $service = app(PaperConversionService::class);
    $quote = $service->quote('USDT', 'BTC', '10.00000000');
    $service->convert($user, 'USDT', 'BTC', '10.00000000', '', $quote['target_quote_at'], 'reused-key');

    expect(fn () => $service->convert($user, 'USDT', 'BTC', '9.00000000', '', $quote['target_quote_at'], 'reused-key'))
        ->toThrow(RuntimeException::class, 'different conversion');
});

test('clients can open the paper conversion workspace', function () {
    $client = User::factory()->create();
    $client->markEmailAsVerified();

    $this->actingAs($client)->get('/convert')
        ->assertOk()
        ->assertSee('Currency conversion')
        ->assertSee('PAPER ONLY');
});


test('conversion preview refreshes the requested market pair from the provider', function () {
    $client = User::factory()->create();
    $client->markEmailAsVerified();

    Illuminate\\Support\\Facades\\Http::fake([
        'api.binance.com/api/v3/ticker/price*' => Illuminate\\Support\\Facades\\Http::response([
            'symbol' => 'BTCUSDT',
            'price' => '60000.000000000000',
        ], 200),
    ]);

    $this->actingAs($client)
        ->postJson('/convert/quote', [
            'from_currency' => 'USD',
            'to_currency' => 'BTC',
            'amount' => '10.00',
        ])
        ->assertOk()
        ->assertJsonPath('from_currency', 'USD')
        ->assertJsonPath('to_currency', 'BTC')
        ->assertJsonPath('target_price', '60000.000000000000');

    expect(DB::table('market_quotes')->where('symbol', 'BTCUSDT')->value('price'))
        ->toBe('60000.000000000000');
});
