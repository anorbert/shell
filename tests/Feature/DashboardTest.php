<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('client accounts can visit the client dashboard', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Two independent wallets')
        ->assertSee('DEMO WALLET')
        ->assertSee('REAL WALLET')
        ->assertSee('Client');
});

test('administrator accounts are redirected to administration', function () {
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('admin.dashboard'));
});

test('clients cannot access administrator routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('administrators can access the administrator dashboard', function () {
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Platform overview');
});

test('public registration creates a client account only', function () {
    $this->post('/register', [
        'name' => 'Test Client',
        'email' => 'client@example.com',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
        'role' => 'admin',
        'country_code' => 'RW',
        'phone_number' => '788123456',
    ])->assertRedirect('/dashboard');

    expect(User::where('email', 'client@example.com')->first()->role)->toBe('client');
});
