<?php

use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Support\Facades\DB;

function createCryptoMethodForTest(array $overrides = []): int
{
    return DB::table('crypto_payment_methods')->insertGetId(array_merge([
        'asset' => 'USDT', 'network' => 'TRC20', 'wallet_address' => 'TTestDepositAddress123456789',
        'deposits_enabled' => true, 'withdrawals_enabled' => true,
        'created_at' => now(), 'updated_at' => now(),
    ], $overrides));
}


function createConfirmedDepositForTest(User $client): void
{
    DB::table('wallet_requests')->insert([
        'user_id' => $client->id, 'type' => 'deposit', 'status' => 'confirmed',
        'amount_usd' => '50.00000000', 'asset' => 'USDT', 'network' => 'TRC20',
        'wallet_address' => 'TTestDepositAddress123456789',
        'transaction_hash' => 'test-confirmed-deposit-'.$client->id,
        'ledger_key' => 'test-confirmed-deposit-ledger-'.$client->id,
        'reviewed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
    ]);
}

function createVerifiedAdminForTest(): User
{
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();
    $admin->markEmailAsVerified();
    return $admin;
}

test('manual crypto deposit stays pending and never credits the real wallet before admin confirmation', function () {
    $client = User::factory()->create();
    $client->markEmailAsVerified();
    $admin = createVerifiedAdminForTest();
    $method = createCryptoMethodForTest();

    $this->actingAs($client)->post('/wallet-requests/deposit', [
        'method_id' => $method, 'amount_usd' => '125.00000000', 'asset_amount' => '125.00000000',
        'transaction_hash' => '0xabcdef1234567890deposit',
    ])->assertRedirect();

    $request = DB::table('wallet_requests')->where('user_id', $client->id)->first();
    $ledger = app(LedgerService::class);
    $wallet = $ledger->realWallet($client);

    expect($request->status)->toBe('pending')
        ->and($ledger->balanceUnits($wallet))->toBe(0);

    expect(DB::table('user_notifications')->where('user_id', $admin->id)->where('type', 'wallet_request')->where('title', 'Deposit request requires confirmation')->exists())->toBeTrue();

    $this->actingAs($admin)->post('/admin/wallet-operations/'.$request->id.'/confirm-deposit', [
        'amount_usd' => '125.00000000', 'admin_note' => 'Verified on-chain.',
    ])->assertRedirect();

    expect(DB::table('wallet_requests')->where('id', $request->id)->value('status'))->toBe('confirmed')
        ->and($ledger->balanceUnits($wallet))->toBe(12500000000);
});

test('clients cannot submit KYC before their first confirmed real deposit', function () {
    $client = User::factory()->create();
    $client->markEmailAsVerified();

    $this->actingAs($client)->post('/wallet-requests/kyc', [])
        ->assertSessionHasErrors(['kyc' => 'Complete and receive confirmation for at least one real-money deposit before submitting KYC.']);

    expect(DB::table('kyc_submissions')->where('user_id', $client->id)->count())->toBe(0);
});

test('withdrawals are blocked until a real deposit has been confirmed', function () {
    $client = User::factory()->create();
    $client->markEmailAsVerified();
    $method = createCryptoMethodForTest();

    $this->actingAs($client)->post('/wallet-requests/withdraw', [
        'method_id' => $method, 'amount_usd' => '25.00000000',
        'wallet_address' => 'TClientReceivingAddress123456789',
    ])->assertSessionHasErrors(['withdrawal' => 'At least one real-money deposit must be successfully confirmed before withdrawals are available.']);

    expect(DB::table('wallet_requests')->where('user_id', $client->id)->count())->toBe(0);
});

test('KYC-verified withdrawal reserves funds and admin marks it paid only with a transaction reference', function () {
    $client = User::factory()->create();
    $client->markEmailAsVerified();
    $method = createCryptoMethodForTest();
    createConfirmedDepositForTest($client);
    DB::table('kyc_submissions')->insert([
        'user_id' => $client->id, 'document_type' => 'national_id',
        'document_path' => 'kyc/test/id.pdf', 'status' => 'verified',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $ledger = app(LedgerService::class);
    $wallet = $ledger->realWallet($client);
    $capital = $ledger->system('TEST_REAL_WALLET_CAPITAL', 'USD', 'debit');
    $ledger->post('test-real-wallet-funding', 'Test real wallet funding', [
        ['account_id' => $capital, 'side' => 'debit', 'amount' => '100.00000000'],
        ['account_id' => $wallet, 'side' => 'credit', 'amount' => '100.00000000'],
    ]);

    $this->actingAs($client)->post('/wallet-requests/withdraw', [
        'method_id' => $method, 'amount_usd' => '30.00000000',
        'wallet_address' => 'TClientReceivingAddress123456789',
    ])->assertRedirect();

    $request = DB::table('wallet_requests')->where('user_id', $client->id)->where('type', 'withdrawal')->first();
    expect($request->status)->toBe('pending')
        ->and($ledger->balanceUnits($wallet))->toBe(7000000000);

    $admin = createVerifiedAdminForTest();
    $this->actingAs($admin)->post('/admin/wallet-operations/'.$request->id.'/mark-paid', [
        'transaction_hash' => '0xabcdef1234567890withdraw', 'settlement_amount' => '0.002500000000',
        'admin_note' => 'Transfer sent and verified.',
    ])->assertRedirect();

    expect(DB::table('wallet_requests')->where('id', $request->id)->value('status'))->toBe('paid')
        ->and($ledger->balanceUnits($wallet))->toBe(7000000000);
});

test('rejecting a withdrawal releases its reserved balance', function () {
    $client = User::factory()->create();
    $client->markEmailAsVerified();
    $method = createCryptoMethodForTest();
    createConfirmedDepositForTest($client);
    $submission = DB::table('kyc_submissions')->insertGetId([
        'user_id' => $client->id, 'document_type' => 'passport',
        'document_path' => 'kyc/test/passport.pdf', 'status' => 'verified',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $ledger = app(LedgerService::class);
    $wallet = $ledger->realWallet($client);
    $capital = $ledger->system('TEST_REAL_WALLET_CAPITAL_REJECT', 'USD', 'debit');
    $ledger->post('test-real-wallet-funding-reject', 'Test real wallet funding', [
        ['account_id' => $capital, 'side' => 'debit', 'amount' => '50.00000000'],
        ['account_id' => $wallet, 'side' => 'credit', 'amount' => '50.00000000'],
    ]);
    $this->actingAs($client)->post('/wallet-requests/withdraw', [
        'method_id' => $method, 'amount_usd' => '20.00000000',
        'wallet_address' => 'TClientReceivingAddress123456789',
    ])->assertRedirect();
    $request = DB::table('wallet_requests')->where('user_id', $client->id)->where('type', 'withdrawal')->first();

    $admin = createVerifiedAdminForTest();
    $this->actingAs($admin)->post('/admin/wallet-operations/'.$request->id.'/reject', [
        'admin_note' => 'Address could not be validated.',
    ])->assertRedirect();

    expect(DB::table('wallet_requests')->where('id', $request->id)->value('status'))->toBe('rejected')
        ->and($ledger->balanceUnits($wallet))->toBe(5000000000);
});


test('admin can search KYC cases and open a dedicated case file', function () {
    $client = User::factory()->create();
    $submission = DB::table('kyc_submissions')->insertGetId([
        'user_id' => $client->id, 'document_type' => 'passport',
        'document_number' => 'P1234567', 'document_path' => 'kyc/test/passport.pdf',
        'document_mime' => 'application/pdf', 'selfie_path' => 'kyc/test/selfie.jpg',
        'selfie_mime' => 'image/jpeg', 'status' => 'pending',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $admin = createVerifiedAdminForTest();

    $this->actingAs($admin)->get('/admin/kyc?status=pending&q='.urlencode($client->email))
        ->assertOk()->assertSee($client->email);
    $this->actingAs($admin)->get('/admin/kyc/'.$submission)
        ->assertOk()->assertSee('Verification checklist')->assertSee('Identity evidence');
});

test('admin cannot approve KYC without completing every required review check', function () {
    $client = User::factory()->create();
    $submission = DB::table('kyc_submissions')->insertGetId([
        'user_id' => $client->id, 'document_type' => 'national_id',
        'document_number' => 'ID123456', 'document_path' => 'kyc/test/id.pdf',
        'document_mime' => 'application/pdf', 'selfie_path' => 'kyc/test/selfie.jpg',
        'selfie_mime' => 'image/jpeg', 'status' => 'pending',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $admin = createVerifiedAdminForTest();

    $this->actingAs($admin)->post('/admin/wallet-operations/kyc/'.$submission, [
        'decision' => 'verified', 'review_note' => 'Documents reviewed.',
        'checks' => ['identity_legible' => '1'],
    ])->assertSessionHasErrors([
        'checks.details_match', 'checks.document_valid', 'checks.selfie_match',
        'checks.duplicate_check', 'checks.risk_screened',
    ]);

    expect(DB::table('kyc_submissions')->where('id', $submission)->value('status'))->toBe('pending');
});
