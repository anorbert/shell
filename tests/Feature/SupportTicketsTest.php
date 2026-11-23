<?php
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('clients can create support tickets and cannot read another clients tickets', function () {
    $owner = User::factory()->create();
    $owner->markEmailAsVerified();
    $this->actingAs($owner)->post('/support', [
        'subject' => 'Unable to open a paper trade',
        'category' => 'trading',
        'message' => 'The market quote is fresh but my paper trade request is rejected.',
    ])->assertRedirect();

    $ticket = DB::table('support_tickets')->where('user_id', $owner->id)->first();
    expect($ticket)->not->toBeNull();
    $this->assertDatabaseHas('support_messages', [
        'support_ticket_id' => $ticket->id,
        'sender_role' => 'client',
    ]);

    $other = User::factory()->create();
    $other->markEmailAsVerified();
    $this->actingAs($other)->get('/support/'.$ticket->id)->assertNotFound();
    $this->actingAs($other)->post('/support/'.$ticket->id.'/messages', ['message' => 'Private reply'])->assertNotFound();
});

test('administrators can reply to support tickets and notify the client', function () {
    $client = User::factory()->create();
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin', 'email_verified_at' => now()])->save();
    $ticketId = DB::table('support_tickets')->insertGetId([
        'user_id' => $client->id,
        'subject' => 'Account question',
        'category' => 'account',
        'status' => 'open',
        'last_message_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)->post("/admin/support/{$ticketId}/messages", [
        'message' => 'We have reviewed your request and resolved it.',
        'status' => 'closed',
    ])->assertRedirect();

    $this->assertDatabaseHas('support_tickets', ['id' => $ticketId, 'status' => 'closed']);
    $this->assertDatabaseHas('support_messages', ['support_ticket_id' => $ticketId, 'sender_role' => 'admin']);
    $this->assertDatabaseHas('user_notifications', ['user_id' => $client->id, 'type' => 'support_reply']);
});
