<?php

use App\Models\User;
use App\Services\LedgerService;

test('registration screen includes international country calling codes', function () {
    $response = $this->get('/register');

    $response->assertOk()
        ->assertSee('Country / region')
        ->assertSee('Mobile phone number')
        ->assertSee('RW', false)
        ->assertSee('+250');
});

test('new users can register with an international phone number', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'country_code' => 'RW',
        'phone_number' => '0788 123 456',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'test@example.com')->firstOrFail();
    expect($user->country_code)->toBe('RW')
        ->and($user->phone_e164)->toBe('+250788123456')
        ->and($user->phone_verified_at)->toBeNull();

    $wallets = app(LedgerService::class)->provisionClientWallets($user);
    expect(app(LedgerService::class)->balanceUnits($wallets['demo']))->toBe(2000000000000)
        ->and(app(LedgerService::class)->balanceUnits($wallets['real']))->toBe(0);
});

test('registration rejects unsupported country codes and malformed phone numbers', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'country_code' => 'ZZ',
        'phone_number' => 'not-a-number',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors(['country_code', 'phone_number']);
});

test('registration accepts an already international phone number when its country matches', function () {
    $response = $this->post('/register', [
        'name' => 'International User',
        'email' => 'international@example.com',
        'country_code' => 'US',
        'phone_number' => '+1 (202) 555-0123',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
    expect(User::where('email', 'international@example.com')->value('phone_e164'))->toBe('+12025550123');
});

test('registration rejects a pasted international number that conflicts with selected country', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'International User',
        'email' => 'mismatch@example.com',
        'country_code' => 'RW',
        'phone_number' => '+12025550123',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/register')->assertSessionHasErrors('phone_number');
});


test('registration form displays an optional referral code from an invitation link', function () {
    User::factory()->create(['role' => 'client', 'referral_code' => 'SHELL12345']);

    $response = $this->get('/register?ref=SHELL12345');

    $response->assertOk()
        ->assertSee('name="referral_code"', false)
        ->assertSee('value="SHELL12345"', false)
        ->assertSee('(optional)');
});

test('new users can manually enter a referral code and are attributed to its owner', function () {
    $referrer = User::factory()->create(['role' => 'client', 'referral_code' => 'FRIEND1234']);

    $response = $this->post('/register', [
        'name' => 'Referred User',
        'email' => 'referred@example.com',
        'country_code' => 'RW',
        'phone_number' => '0788 123 456',
        'referral_code' => ' friend1234 ',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
    $user = User::where('email', 'referred@example.com')->firstOrFail();
    expect((int) $user->referred_by_user_id)->toBe((int) $referrer->id);
});

test('registration rejects an invalid manual referral code', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Unknown Referral',
        'email' => 'unknown-referral@example.com',
        'country_code' => 'RW',
        'phone_number' => '0788 123 456',
        'referral_code' => 'NOTAREALCODE',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/register')->assertSessionHasErrors('referral_code');
    expect(User::where('email', 'unknown-referral@example.com')->exists())->toBeFalse();
});
