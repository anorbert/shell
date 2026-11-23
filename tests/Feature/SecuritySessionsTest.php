<?php
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
test('clients can revoke other database sessions after password confirmation', function () {
    config(['session.driver' => 'database']);
    $user = User::factory()->create(['password' => Hash::make('correct-password')]);
    $user->markEmailAsVerified();
    $this->actingAs($user);
    $otherSession = 'other-device-session-123';
    DB::table('sessions')->insert([
        'id' => $otherSession,
        'user_id' => $user->id,
        'ip_address' => '192.0.2.10',
        'user_agent' => 'Test browser',
        'payload' => '',
        'last_activity' => time(),
    ]);
    $rememberToken = $user->fresh()->remember_token;

    $this->postJson('/security/sessions/revoke-others', ['password' => 'correct-password'])
        ->assertOk()->assertJson(['message' => 'Other sessions and remembered-device tokens have been revoked.']);

    $this->assertDatabaseMissing('sessions', ['id' => $otherSession]);
    expect($user->fresh()->remember_token)->not->toBe($rememberToken);
});

test('clients cannot revoke another users session', function () {
    config(['session.driver' => 'database']);
    $owner = User::factory()->create();
    $owner->markEmailAsVerified();
    $other = User::factory()->create();
    $this->actingAs($other);
    DB::table('sessions')->insert([
        'id' => 'private-owner-session',
        'user_id' => $owner->id,
        'ip_address' => '192.0.2.11',
        'user_agent' => 'Private browser',
        'payload' => '',
        'last_activity' => time(),
    ]);

    $this->deleteJson('/security/sessions/private-owner-session')->assertNotFound();
    $this->assertDatabaseHas('sessions', ['id' => 'private-owner-session', 'user_id' => $owner->id]);
});

test('clients can view their account security page', function () {
    $client = User::factory()->create();
    $client->markEmailAsVerified();

    $this->actingAs($client)->get('/security')
        ->assertOk()
        ->assertSee('Protect your account')
        ->assertSee('Login history');
});
