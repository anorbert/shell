<?php

use App\\Models\\User;
use App\\Services\\LedgerService;
use App\\Services\\PaperTradingService;
use Illuminate\\Support\\Facades\\DB;

test('paper wallet credits create balanced ledger entries', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();
    app(LedgerService::class)->creditPaperWallet($user, '100.00000000');

    $wallet = app(LedgerService::class)->wallet($user);
    expect(app(LedgerService::class)->balanceUnits($wallet))->toBe(10000000000);

    $transaction = DB::table('ledger_transactions')->first();
    $entries = DB::table('ledger_entries')->where('ledger_transaction_id', $transaction->id)->get();
    expect($entries)->toHaveCount(2);
});

test('ledger idempotency key returns the original transaction without reposting', function () {
    $user = User::factory()->create();
    $ledger = app(LedgerService::class);
    $wallet = $ledger->wallet($user);
    $capital = $ledger->system('TEST_CAPITAL', 'USDT', 'debit');
    $entries = [
        ['account_id' => $capital, 'side' => 'debit', 'amount' => '25.00000000'],
        ['account_id' => $wallet, 'side' => 'credit', 'amount' => '25.00000000'],
    ];

    $first = $ledger->post('test-credit-once', 'Test credit', $entries);
    $second = $ledger->post('test-credit-once', 'Test credit', $entries);

    expect($second)->toBe($first)
        ->and(DB::table('ledger_transactions')->where('idempotency_key', 'test-credit-once')->count())->toBe(1)
        ->and($ledger->balanceUnits($wallet))->toBe(2500000000);
});

test('market and trading screens require authentication', function () {
    $this->get('/markets')->assertRedirect('/login');
    $this->get('/trading')->assertRedirect('/login');
    $this->get('/portfolio')->assertRedirect('/login');
});

test('client portfolio displays zero until simulation credits are issued', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/portfolio')->assertOk()->assertSee('0.00000000')->assertSee('Paper wallet only');
});

test('paper trade opening is blocked when quote is missing', function () {
    $user = User::factory()->create();
    app(LedgerService::class)->creditPaperWallet($user, '100.00000000');

    $this->actingAs($user)->postJson('/trading/paper', [
        'symbol' => 'BTCUSDT',
        'direction' => 'UP',
        'stake' => '10',
        'duration' => 60,
        'request_key' => 'test-open-no-quote',
    ])->assertStatus(422)->assertJsonFragment([
        'message' => 'No fresh market quote is available for this pair. Trading is blocked.',
    ]);
});

test('paper trade cannot be opened without sufficient simulation funds', function () {
    $user = User::factory()->create();
    DB::table('market_quotes')->insert([
        'symbol' => 'BTCUSDT',
        'price' => '90000.000000000000',
        'provider' => 'Test provider',
        'captured_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)->postJson('/trading/paper', [
        'symbol' => 'BTCUSDT',
        'direction' => 'UP',
        'stake' => '10',
        'duration' => 60,
        'request_key' => 'test-insufficient',
    ])->assertUnprocessable();

    expect(DB::table('paper_trades')->count())->toBe(0);
});

test('paper trade opens and reserves the stake from the client wallet', function () {
    $user = User::factory()->create();
    $ledger = app(LedgerService::class);
    $ledger->creditPaperWallet($user, '100.00000000');

    DB::table('market_quotes')->insert([
        'symbol' => 'BTCUSDT',
        'price' => '90000.000000000000',
        'provider' => 'Test provider',
        'captured_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)->postJson('/trading/paper', [
        'symbol' => 'BTCUSDT',
        'direction' => 'UP',
        'stake' => '10',
        'duration' => 60,
        'request_key' => 'test-open-once',
    ])->assertCreated();

    expect(DB::table('paper_trades')->where('user_id', $user->id)->count())->toBe(1)
        ->and($ledger->balanceUnits($ledger->wallet($user)))->toBe(9000000000);
});

test('a trade is not settled using a quote captured before expiry', function () {
    $user = User::factory()->create();
    $ledger = app(LedgerService::class);
    $ledger->creditPaperWallet($user, '100.00000000');

    $openedAt = now()->subMinutes(3);
    DB::table('market_quotes')->insert([
        'symbol' => 'BTCUSDT',
        'price' => '90000.000000000000',
        'provider' => 'Test provider',
        'captured_at' => $openedAt,
        'created_at' => $openedAt,
        'updated_at' => $openedAt,
    ]);
    DB::table('paper_trades')->insert([
        'user_id' => $user->id,
        'symbol' => 'BTCUSDT',
        'direction' => 'UP',
        'stake' => '10.00000000',
        'opening_price' => '90000.000000000000',
        'duration_seconds' => 60,
        'status' => 'open',
        'open_idempotency_key' => 'old-trade-waits-for-post-expiry-price',
        'opened_at' => $openedAt,
        'expires_at' => $openedAt->copy()->addMinute(),
        'created_at' => $openedAt,
        'updated_at' => $openedAt,
    ]);

    expect(app(PaperTradingService::class)->settleDue())->toBe(0)
        ->and(DB::table('paper_trades')->where('open_idempotency_key', 'old-trade-waits-for-post-expiry-price')->value('status'))->toBe('open');
});

test('an expired winning paper trade settles once using a post-expiry quote', function () {
    $user = User::factory()->create();
    $ledger = app(LedgerService::class);
    $ledger->creditPaperWallet($user, '100.00000000');

    $openingAt = now()->subMinutes(3);
    DB::table('market_quotes')->insert([
        'symbol' => 'BTCUSDT',
        'price' => '90000.000000000000',
        'provider' => 'Test provider',
        'captured_at' => $openingAt,
        'created_at' => $openingAt,
        'updated_at' => $openingAt,
    ]);

    $tradeId = app(PaperTradingService::class)->open($user, 'BTCUSDT', 'UP', '10.00000000', 60, 'test-winning-trade');
    $expiredAt = now()->subMinute();
    DB::table('paper_trades')->where('id', $tradeId)->update([
        'expires_at' => $expiredAt,
        'opened_at' => $expiredAt->copy()->subMinute(),
    ]);
    DB::table('market_quotes')->where('symbol', 'BTCUSDT')->update([
        'price' => '91000.000000000000',
        'captured_at' => now(),
        'updated_at' => now(),
    ]);

    expect(app(PaperTradingService::class)->settleDue())->toBe(1)
        ->and(DB::table('paper_trades')->where('id', $tradeId)->value('status'))->toBe('won')
        ->and($ledger->balanceUnits($ledger->wallet($user)))->toBe(10800000000)
        ->and(app(PaperTradingService::class)->settleDue())->toBe(0)
        ->and(DB::table('ledger_transactions')->where('idempotency_key', 'paper-settle:' . $tradeId)->count())->toBe(1);
});


test('ledger refuses a posting that would make a paper wallet negative', function () {
    $user = User::factory()->create();
    $ledger = app(LedgerService::class);
    $wallet = $ledger->wallet($user);
    $capital = $ledger->system('NEGATIVE_TEST_CAPITAL', 'USDT', 'debit');
    $ledger->creditPaperWallet($user, '5.00000000');

    expect(fn () => $ledger->post('negative-wallet-test', 'Invalid overdraw', [
        ['account_id' => $wallet, 'side' => 'debit', 'amount' => '6.00000000'],
        ['account_id' => $capital, 'side' => 'credit', 'amount' => '6.00000000'],
    ]))->toThrow(RuntimeException::class, 'Insufficient paper balance; ledger posting would create a negative wallet balance.');

    expect($ledger->balanceUnits($wallet))->toBe(500000000);
});
