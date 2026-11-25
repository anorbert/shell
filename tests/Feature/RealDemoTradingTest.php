<?php

use App\Models\User;
use App\Services\LedgerService;
use App\Services\MarketQuoteService;
use App\Services\PaperTradingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

function configureTradingQuoteForEnvironmentTests(): void
{
    config([
        'shell.paper.enabled' => true,
        'shell.paper.currency' => 'USD',
        'shell.paper.max_stake' => '1000.00000000',
        'shell.paper.durations' => [60],
        'shell.paper.payout_basis_points' => 18000,
        'shell.market.symbols' => ['BTCUSDT'],
    ]);

    $markets = \\Mockery::mock(MarketQuoteService::class);
    $markets->shouldReceive('freshExecutionQuote')->andReturn((object) [
        'symbol' => 'BTCUSDT',
        'price' => '65000.000000000000',
        'captured_at' => now('UTC')->toDateTimeString(),
    ]);
    $markets->shouldReceive('refreshSymbolQuote')->andReturn(true);
    app()->instance(MarketQuoteService::class, $markets);
}

function createConfirmedTradingDeposit(User $user): void
{
    DB::table('wallet_requests')->insert([
        'user_id' => $user->id,
        'type' => 'deposit',
        'status' => 'confirmed',
        'amount_usd' => '100.00000000',
        'asset' => 'USDT',
        'network' => 'TRC20',
        'wallet_address' => 'TTestDepositAddress123456789',
        'transaction_hash' => 'test-trading-deposit-'.$user->id,
        'ledger_key' => 'test-trading-deposit-ledger-'.$user->id,
        'reviewed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function fundRealTradingWallet(User $user, LedgerService $ledger, string $amount = '100.00000000'): void
{
    $wallet = $ledger->realWallet($user, 'USD');
    $capital = $ledger->system('TEST_REAL_WALLET_CAPITAL', 'USD', 'debit');
    $ledger->post('test-trading-deposit-ledger:'.$user->id, 'Test confirmed real deposit credit', [
        ['account_id' => $capital, 'side' => 'debit', 'amount' => $amount],
        ['account_id' => $wallet, 'side' => 'credit', 'amount' => $amount],
    ], ['user_id' => $user->id, 'wallet_request_id' => null, 'type' => 'manual_deposit_test']);
}

function fundRealPayoutReserveForTest(LedgerService $ledger, string $amount = '1000.00000000'): void
{
    $reserve = $ledger->system('REAL_PAYOUT_RESERVE', 'USD', 'credit');
    $capital = $ledger->system('TEST_REAL_PAYOUT_CAPITAL', 'USD', 'debit');
    $ledger->post('test-real-payout-reserve-fund', 'Test verified reserve funding', [
        ['account_id' => $capital, 'side' => 'debit', 'amount' => $amount],
        ['account_id' => $reserve, 'side' => 'credit', 'amount' => $amount],
    ], ['test_funding' => true]);
}

test('demo trading remains available without a deposit or KYC', function () {
    configureTradingQuoteForEnvironmentTests();
    $user = User::factory()->create();
    $ledger = app(LedgerService::class);
    $wallet = $ledger->provisionClientWallets($user, 'USD')['demo'];

    $tradeId = app(PaperTradingService::class)->open(
        $user, 'BTCUSDT', 'UP', '10.00000000', 60, 'demo-no-kyc-no-deposit', 'demo'
    );

    expect(DB::table('paper_trades')->where('id', $tradeId)->value('account_type'))->toBe('demo')
        ->and($ledger->balanceUnits($wallet))->toBe(1999000000000)
        ->and(DB::table('wallet_requests')->where('user_id', $user->id)->count())->toBe(0);
});

test('pending deposit or transaction hash does not unlock real trading', function () {
    configureTradingQuoteForEnvironmentTests();
    $user = User::factory()->create();
    DB::table('wallet_requests')->insert([
        'user_id' => $user->id,
        'type' => 'deposit',
        'status' => 'pending',
        'amount_usd' => '100.00000000',
        'asset' => 'USDT',
        'network' => 'TRC20',
        'wallet_address' => 'TTestDepositAddress123456789',
        'transaction_hash' => 'pending-deposit-hash-'.$user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => app(PaperTradingService::class)->open(
        $user, 'BTCUSDT', 'UP', '10.00000000', 60, 'pending-real-trade', 'real'
    ))->toThrow(RuntimeException::class, 'Real trading unlocks after your first deposit has been verified and credited to your real wallet.');
});

test('confirmed deposit and funded reserve enable real trading without KYC', function () {
    configureTradingQuoteForEnvironmentTests();
    $user = User::factory()->create();
    createConfirmedTradingDeposit($user);
    $ledger = app(LedgerService::class);
    $ledger->provisionClientWallets($user, 'USD');
    fundRealTradingWallet($user, $ledger);
    fundRealPayoutReserveForTest($ledger);

    $demoWallet = $ledger->demoWallet($user, 'USD');
    $realWallet = $ledger->realWallet($user, 'USD');
    $tradeId = app(PaperTradingService::class)->open(
        $user, 'BTCUSDT', 'UP', '10.00000000', 60, 'funded-real-trade', 'real'
    );

    expect(DB::table('paper_trades')->where('id', $tradeId)->value('account_type'))->toBe('real')
        ->and((int) DB::table('paper_trades')->where('id', $tradeId)->value('payout_basis_points'))->toBe(18000)
        ->and($ledger->balanceUnits($realWallet))->toBe(9000000000)
        ->and($ledger->balanceUnits($demoWallet))->toBe(2000000000000)
        ->and(DB::table('kyc_submissions')->where('user_id', $user->id)->count())->toBe(0);
});

test('real trading fails closed when the payout reserve cannot cover maximum winning profit', function () {
    configureTradingQuoteForEnvironmentTests();
    $user = User::factory()->create();
    createConfirmedTradingDeposit($user);
    $ledger = app(LedgerService::class);
    $ledger->provisionClientWallets($user, 'USD');
    fundRealTradingWallet($user, $ledger);

    expect(fn () => app(PaperTradingService::class)->open(
        $user, 'BTCUSDT', 'UP', '10.00000000', 60, 'unfunded-real-reserve-trade', 'real'
    ))->toThrow(RuntimeException::class, 'Real trading is temporarily unavailable because the funded payout reserve is insufficient for this position.');

    expect(DB::table('paper_trades')->count())->toBe(0)
        ->and($ledger->balanceUnits($ledger->realWallet($user, 'USD')))->toBe(10000000000);
});

test('real winning settlement pays the real wallet using the opening payout snapshot', function () {
    configureTradingQuoteForEnvironmentTests();
    $user = User::factory()->create();
    createConfirmedTradingDeposit($user);
    $ledger = app(LedgerService::class);
    $ledger->provisionClientWallets($user, 'USD');
    fundRealTradingWallet($user, $ledger);
    fundRealPayoutReserveForTest($ledger);

    $tradeId = app(PaperTradingService::class)->open(
        $user, 'BTCUSDT', 'UP', '10.00000000', 60, 'real-win-settlement', 'real'
    );
    $trade = DB::table('paper_trades')->where('id', $tradeId)->first();

    Carbon::setTestNow(Carbon::parse($trade->expires_at, 'UTC')->addSecond());
    DB::table('market_quote_snapshots')->insert([
        'symbol' => 'BTCUSDT',
        'price' => '66000.000000000000',
        'captured_at' => now('UTC')->toDateTimeString(),
    ]);

    // Changing the live setting after opening must not alter this trade's terms.
    config(['shell.paper.payout_basis_points' => 10000]);
    expect(app(PaperTradingService::class)->settleDue())->toBe(1);

    $settled = DB::table('paper_trades')->where('id', $tradeId)->first();
    $realWallet = $ledger->realWallet($user, 'USD');
    $reserve = $ledger->system('REAL_PAYOUT_RESERVE', 'USD', 'credit');
    $profitEscrow = $ledger->system('REAL_PAYOUT_ESCROW', 'USD', 'credit');

    expect($settled->status)->toBe('won')
        ->and((int) $settled->payout_basis_points)->toBe(18000)
        ->and($ledger->balanceUnits($realWallet))->toBe(10800000000)
        ->and($ledger->normalBalanceUnits($reserve))->toBe(99200000000)
        ->and($ledger->normalBalanceUnits($profitEscrow))->toBe(0);

    Carbon::setTestNow();
});

test('a confirmed request without its matching real-wallet ledger credit does not unlock real trading', function () {
    configureTradingQuoteForEnvironmentTests();
    $user = User::factory()->create();
    createConfirmedTradingDeposit($user);

    expect(fn () => app(PaperTradingService::class)->open(
        $user, 'BTCUSDT', 'UP', '10.00000000', 60, 'confirmed-without-ledger', 'real'
    ))->toThrow(RuntimeException::class, 'Real trading unlocks after your first deposit has been verified and credited to your real wallet.');
});

test('admin payout reserve funding is audited and idempotent by external custody reference', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();
    $admin->markEmailAsVerified();

    $payload = [
        'amount_usd' => '500.00000000',
        'external_reference' => 'custody-proof-reference-001',
        'admin_note' => 'External treasury balance verified and segregated.',
    ];

    $this->actingAs($admin)->post('/admin/wallet-operations/real-payout-reserve', $payload)->assertRedirect();
    $this->actingAs($admin)->post('/admin/wallet-operations/real-payout-reserve', $payload)->assertRedirect();

    $ledger = app(LedgerService::class);
    $reserve = $ledger->system('REAL_PAYOUT_RESERVE', 'USD', 'credit');

    expect($ledger->normalBalanceUnits($reserve))->toBe(50000000000)
        ->and(DB::table('ledger_transactions')->where('idempotency_key', 'like', 'real-reserve-fund:%')->count())->toBe(1);
});
