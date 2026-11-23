<?php

use App\Services\LedgerService;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $wallets = app(LedgerService::class)->provisionClientWallets(auth()->user());
    expect(app(LedgerService::class)->balanceUnits($wallets['demo']))->toBe(2000000000000)
        ->and(app(LedgerService::class)->balanceUnits($wallets['real']))->toBe(0);
});
