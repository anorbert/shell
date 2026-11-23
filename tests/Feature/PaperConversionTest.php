<?php
use App\Models\User;
use App\Services\LedgerService;
use App\Services\PaperConversionService;
use Illuminate\Support\Facades\DB;

test('paper conversions move balances through balanced currency ledgers and are idempotent', function () {
    $user = User::factory()->create();
    $ledger = app(LedgerService::class);
    $ledger->creditPaperWallet($user, '10.00000000');

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
        ->and($conversion->amount_out)->toBe('0.00020000')
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
    $ledger->creditPaperWallet($user, '10.00000000');

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
        'captured_at' => now()->addSecond(),
        'updated_at' => now()->addSecond(),
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
