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
