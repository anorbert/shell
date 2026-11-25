<?php

use Illuminate\Support\Facades\DB;

test('a public visitor can start a private support conversation and continue it in the same session', function () {
    $response = $this->postJson('/support/chat', [
        'name' => 'Test Visitor',
        'email' => 'visitor@example.com',
        'category' => 'account',
        'message' => 'I need help understanding my account.',
        'website' => '',
    ]);

    $response->assertCreated()
        ->assertJsonPath('messages.0.sender', 'You')
        ->assertJsonPath('messages.0.role', 'guest');

    $ticketId = $response->json('ticket_id');

    $this->assertDatabaseHas('support_tickets', [
        'id' => $ticketId,
        'user_id' => null,
        'guest_name' => 'Test Visitor',
        'guest_email' => 'visitor@example.com',
    ]);

    $this->getJson('/support/chat/'.$ticketId)
        ->assertOk()
        ->assertJsonPath('messages.0.message', 'I need help understanding my account.');

    $this->postJson('/support/chat/'.$ticketId.'/messages', [
        'message' => 'I am adding one more detail.',
    ])->assertOk()->assertJsonPath('messages.1.role', 'guest');

    $this->assertDatabaseHas('support_messages', [
        'support_ticket_id' => $ticketId,
        'sender_role' => 'guest',
        'message' => 'I am adding one more detail.',
    ]);
});

test('a visitor cannot read another visitors conversation by guessing its ticket id', function () {
    $first = $this->postJson('/support/chat', [
        'name' => 'First Visitor',
        'email' => 'first@example.com',
        'category' => 'other',
        'message' => 'My private support request.',
    ])->assertCreated();

    $ticketId = $first->json('ticket_id');

    $this->flushSession();

    $this->getJson('/support/chat/'.$ticketId)->assertNotFound();
    $this->postJson('/support/chat/'.$ticketId.'/messages', [
        'message' => 'Trying to access another conversation.',
    ])->assertNotFound();
});

test('a closed visitor conversation cannot accept more messages', function () {
    $response = $this->postJson('/support/chat', [
        'name' => 'Visitor',
        'email' => 'visitor@example.com',
        'category' => 'trading',
        'message' => 'Please help with a trade question.',
    ])->assertCreated();

    $ticketId = $response->json('ticket_id');
    DB::table('support_tickets')->where('id', $ticketId)->update(['status' => 'closed']);

    $this->postJson('/support/chat/'.$ticketId.'/messages', [
        'message' => 'A follow-up message.',
    ])->assertStatus(409);
});
