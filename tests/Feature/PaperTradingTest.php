<?php

use App\Models\User;
use App\Services\LedgerService;
use App\Services\PaperTradingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

test('paper wallet credits create balanced ledger entries', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();
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
    $user->markEmailAsVerified();
    $ledger = app(LedgerService::class);
    $wallet = $ledger->wallet($user);
    $capital = $ledger->system('TEST_CAPITAL', 'USD', 'debit');
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

test('client portfolio starts with a $20,000 demo balance and an empty real wallet', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();
    $this->actingAs($user)->get('/portfolio')->assertOk()->assertSee('20000.00000000')->assertSee('0.00000000')->assertSee('Add demo funds');
});

test('paper trade opening is blocked when quote is missing and provider refresh fails', function () {
    Http::fake([
        'api.binance.com/api/v3/ticker/price' => Http::response([], 200),
    ]);

    $user = User::factory()->create();
    $user->markEmailAsVerified();
    app(LedgerService::class)->creditPaperWallet($user, '100.00000000');

    $this->actingAs($user)->postJson('/trading/paper', [
        'symbol' => 'BTCUSDT',
        'direction' => 'UP',
        'stake' => '10',
        'duration' => 60,
        'request_key' => 'test-open-no-quote',
    ])->assertStatus(422)->assertJsonFragment([
        'message' => 'Live market data is temporarily unavailable for this pair. Please retry in a moment. Your stake has not been deducted.',
    ]);
});

test('stale market quotes are refreshed from the provider before a trade is opened', function () {
    Http::fake([
        'api.binance.com/api/v3/ticker/price' => Http::response([
            ['symbol' => 'BTCUSDT', 'price' => '92500.50000000'],
            ['symbol' => 'ETHUSDT', 'price' => '3100.25000000'],
        ], 200),
    ]);

    $user = User::factory()->create();
    $user->markEmailAsVerified();
    $ledger = app(LedgerService::class);
    $ledger->creditPaperWallet($user, '100.00000000');

    $staleAt = now()->subMinutes(5);
    DB::table('market_quotes')->insert([
        'symbol' => 'BTCUSDT',
        'price' => '90000.000000000000',
        'provider' => 'Binance Spot',
        'captured_at' => $staleAt,
        'created_at' => $staleAt,
        'updated_at' => $staleAt,
    ]);

    $this->actingAs($user)->postJson('/trading/paper', [
        'symbol' => 'BTCUSDT',
        'direction' => 'UP',
        'stake' => '10',
        'duration' => 60,
        'request_key' => 'test-open-after-stale-quote-refresh',
    ])->assertCreated()
        ->assertJsonPath('trade.status', 'open')
        ->assertJsonPath('trade.opening_price', '92500.500000000000');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/v3/ticker/price'));
    expect(DB::table('paper_trades')->where('user_id', $user->id)->count())->toBe(1);
});

test('paper trade cannot be opened without sufficient simulation funds', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();
    $ledger = app(LedgerService::class);
    $wallets = $ledger->provisionClientWallets($user);
    $capital = $ledger->system('TEST_EMPTY_DEMO_CAPITAL', 'USD', 'credit');
    $ledger->post('empty-demo-before-trade', 'Test: clear demo wallet', [
        ['account_id' => $wallets['demo'], 'side' => 'debit', 'amount' => '20000.00000000'],
        ['account_id' => $capital, 'side' => 'credit', 'amount' => '20000.00000000'],
    ]);
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
    $user->markEmailAsVerified();
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
    ])->assertCreated()
        ->assertJsonPath('trade.status', 'open')
        ->assertJsonPath('trade.symbol', 'BTCUSDT')
        ->assertJsonPath('trade.direction', 'UP')
        ->assertJsonPath('wallet_balance', '20090.00000000');

    expect(DB::table('paper_trades')->where('user_id', $user->id)->count())->toBe(1)
        ->and($ledger->balanceUnits($ledger->wallet($user)))->toBe(2009000000000);
});

test('a trade is not settled using a quote captured before expiry', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();
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

test('expired trades settle when the first verified post-expiry quote arrives after the former grace period', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();
    $ledger = app(LedgerService::class);
    $ledger->creditPaperWallet($user, '100.00000000');

    $openingAt = now();
    DB::table('market_quotes')->insert([
        'symbol' => 'BTCUSDT',
        'price' => '90000.000000000000',
        'provider' => 'Test provider',
        'captured_at' => $openingAt,
        'created_at' => $openingAt,
        'updated_at' => $openingAt,
    ]);

    $tradeId = app(PaperTradingService::class)->open($user, 'BTCUSDT', 'UP', '10.00000000', 60, 'test-delayed-expiry-quote');
    $expiredAt = now()->subMinutes(7);
    DB::table('paper_trades')->where('id', $tradeId)->update([
        'opened_at' => $expiredAt->copy()->subMinute(),
        'expires_at' => $expiredAt,
    ]);

    // The only available post-expiry snapshot is seven minutes late. The former
    // five-minute cutoff selected this earliest snapshot, rejected it forever,
    // and left the position open even when newer quotes were available.
    DB::table('market_quote_snapshots')->insert([
        'symbol' => 'BTCUSDT',
        'price' => '91000.000000000000',
        'captured_at' => now(),
    ]);

    expect(app(PaperTradingService::class)->settleDue())->toBe(1)
        ->and(DB::table('paper_trades')->where('id', $tradeId)->value('status'))->toBe('won')
        ->and((float) DB::table('paper_trades')->where('id', $tradeId)->value('closing_price'))->toBe(91000.0)
        ->and($ledger->balanceUnits($ledger->wallet($user)))->toBe(2010800000000)
        ->and(app(PaperTradingService::class)->settleDue())->toBe(0)
        ->and(DB::table('ledger_transactions')->where('idempotency_key', 'paper-settle:' . $tradeId)->count())->toBe(1);
});

test('an expired winning paper trade settles once using a post-expiry quote', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();
    $ledger = app(LedgerService::class);
    $ledger->creditPaperWallet($user, '100.00000000');

    $openingAt = now();
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
        ->and($ledger->balanceUnits($ledger->wallet($user)))->toBe(2010800000000)
        ->and(app(PaperTradingService::class)->settleDue())->toBe(0)
        ->and(DB::table('ledger_transactions')->where('idempotency_key', 'paper-settle:' . $tradeId)->count())->toBe(1);
});


test('ledger refuses a posting that would make a paper wallet negative', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();
    $ledger = app(LedgerService::class);
    $wallet = $ledger->wallet($user);
    $capital = $ledger->system('NEGATIVE_TEST_CAPITAL', 'USD', 'debit');
    $ledger->creditPaperWallet($user, '5.00000000');

    expect(fn () => $ledger->post('negative-wallet-test', 'Invalid overdraw', [
        ['account_id' => $wallet, 'side' => 'debit', 'amount' => '6.00000000'],
        ['account_id' => $capital, 'side' => 'credit', 'amount' => '6.00000000'],
    ]))->toThrow(RuntimeException::class, 'Insufficient paper balance; ledger posting would create a negative wallet balance.');

    expect($ledger->balanceUnits($wallet))->toBe(500000000);
});


test('every client receives separate demo and real wallets with independent balances', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();

    $ledger = app(LedgerService::class);
    $wallets = $ledger->provisionClientWallets($user);

    expect($wallets['demo'])->not->toBe($wallets['real'])
        ->and($ledger->balanceUnits($wallets['demo']))->toBe(2000000000000)
        ->and($ledger->balanceUnits($wallets['real']))->toBe(0);

    $ledger->creditPaperWallet($user, '75.00000000');

    expect($ledger->balanceUnits($wallets['demo']))->toBe(2007500000000)
        ->and($ledger->balanceUnits($wallets['real']))->toBe(0);

    $realCapital = $ledger->system('TEST_REAL_CAPITAL', 'USD', 'debit');
    $ledger->post('test-real-wallet-credit', 'Test real wallet ledger funding', [
        ['account_id' => $realCapital, 'side' => 'debit', 'amount' => '20.00000000'],
        ['account_id' => $wallets['real'], 'side' => 'credit', 'amount' => '20.00000000'],
    ], ['environment' => 'real', 'test_only' => true]);

    expect($ledger->balanceUnits($wallets['demo']))->toBe(2007500000000)
        ->and($ledger->balanceUnits($wallets['real']))->toBe(2000000000);
});

test('real wallet cannot be overdrawn by a ledger posting', function () {
    $user = User::factory()->create();
    $ledger = app(LedgerService::class);
    $realWallet = $ledger->realWallet($user);
    $capital = $ledger->system('REAL_OVERDRAW_TEST', 'USD', 'debit');

    expect(fn () => $ledger->post('real-wallet-overdraw-test', 'Invalid real wallet debit', [
        ['account_id' => $realWallet, 'side' => 'debit', 'amount' => '1.00000000'],
        ['account_id' => $capital, 'side' => 'credit', 'amount' => '1.00000000'],
    ]))->toThrow(RuntimeException::class, 'Insufficient real wallet balance; ledger posting would create a negative balance.');
});


test('client can top up demo funds but the real wallet remains unchanged', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();

    $this->actingAs($user)->postJson('/portfolio/demo/top-up', [
        'amount' => '500.00',
        'request_key' => '2c6e3b4a-1c2d-4a5b-9c7d-8e9f0a1b2c3d',
    ])->assertCreated()->assertJsonPath('balance', '20500.00000000');

    $ledger = app(LedgerService::class);
    $wallets = $ledger->provisionClientWallets($user);

    expect($ledger->balanceUnits($wallets['demo']))->toBe(2050000000000)
        ->and($ledger->balanceUnits($wallets['real']))->toBe(0);
});

test('client cannot top up below the demo minimum and has no real withdrawal route', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();

    $this->actingAs($user)->postJson('/portfolio/demo/top-up', [
        'amount' => '50',
        'request_key' => '3d6e3b4a-1c2d-4a5b-9c7d-8e9f0a1b2c3d',
    ])->assertUnprocessable();

    $this->actingAs($user)->postJson('/portfolio/real/withdraw', [
        'amount' => '1',
    ])->assertNotFound();

    $ledger = app(LedgerService::class);
    $wallets = $ledger->provisionClientWallets($user);
    expect($ledger->balanceUnits($wallets['demo']))->toBe(2000000000000)
        ->and($ledger->balanceUnits($wallets['real']))->toBe(0);
});
