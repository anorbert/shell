<?php

namespace App\Http\Controllers\Guests;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SupportChatController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'category' => ['required', 'in:account,trading,market_data,security,other'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
            'website' => ['nullable', 'max:0'],
        ]);

        $token = Str::random(64);
        $now = now();
        $ticketId = DB::transaction(function () use ($data, $token, $now) {
            $id = DB::table('support_tickets')->insertGetId([
                'user_id' => null,
                'guest_name' => trim($data['name']),
                'guest_email' => strtolower(trim($data['email'])),
                'guest_access_token_hash' => hash('sha256', $token),
                'subject' => 'Website enquiry',
                'category' => $data['category'],
                'status' => 'open',
                'last_message_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('support_messages')->insert([
                'support_ticket_id' => $id,
                'user_id' => null,
                'sender_role' => 'guest',
                'message' => trim($data['message']),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $id;
        });

        $request->session()->put($this->sessionKey($ticketId), $token);
        $this->sendConversationCopies($ticketId, 'New website support enquiry');

        return response()->json([
            'ticket_id' => $ticketId,
            'messages' => $this->conversation($ticketId),
            'message' => 'Your message has been sent. Our support team will reply here and by email.',
        ], 201);
    }

    public function messages(Request $request, int $ticket): JsonResponse
    {
        $record = $this->authorizedTicket($request, $ticket);
        abort_unless($record, 404);

        return response()->json([
            'ticket_id' => $record->id,
            'status' => $record->status,
            'messages' => $this->conversation($ticket),
        ]);
    }

    public function reply(Request $request, int $ticket): JsonResponse
    {
        $record = $this->authorizedTicket($request, $ticket);
        abort_unless($record, 404);
        abort_if($record->status === 'closed', 409, 'This conversation is closed. Please start a new chat.');

        $data = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:5000'],
        ]);

        $now = now();
        DB::transaction(function () use ($record, $data, $now) {
            DB::table('support_messages')->insert([
                'support_ticket_id' => $record->id,
                'user_id' => null,
                'sender_role' => 'guest',
                'message' => trim($data['message']),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('support_tickets')->where('id', $record->id)->update([
                'status' => 'pending',
                'last_message_at' => $now,
                'updated_at' => $now,
            ]);
        });

        $this->sendConversationCopies($ticket, 'New visitor message');

        return response()->json(['messages' => $this->conversation($ticket)]);
    }

    private function authorizedTicket(Request $request, int $ticket): ?object
    {
        $record = DB::table('support_tickets')->where('id', $ticket)->first();
        $token = $request->session()->get($this->sessionKey($ticket));

        if (!$record || !$token || !$record->guest_access_token_hash ||
            !hash_equals($record->guest_access_token_hash, hash('sha256', $token))) {
            return null;
        }

        return $record;
    }

    private function sessionKey(int $ticket): string
    {
        return 'guest_support_tokens.'.$ticket;
    }

    private function conversation(int $ticket): array
    {
        return DB::table('support_messages')
            ->where('support_ticket_id', $ticket)
            ->orderBy('id')
            ->get(['id', 'sender_role', 'message', 'created_at'])
            ->map(fn ($message) => [
                'id' => $message->id,
                'sender' => $message->sender_role === 'admin' ? 'SHELL Support' : 'You',
                'role' => $message->sender_role,
                'message' => $message->message,
                'created_at' => $message->created_at,
            ])->all();
    }

    private function sendConversationCopies(int $ticketId, string $subject): void
    {
        $ticket = DB::table('support_tickets')->where('id', $ticketId)->first();
        if (!$ticket) {
            return;
        }

        $messages = DB::table('support_messages')->where('support_ticket_id', $ticketId)->orderBy('id')->get();
        $lines = [
            'SHELL SUPPORT CONVERSATION',
            'Ticket: #'.$ticketId,
            'Visitor: '.$ticket->guest_name,
            'Email: '.$ticket->guest_email,
            'Category: '.$ticket->category,
            'Status: '.$ticket->status,
            '',
        ];

        foreach ($messages as $message) {
            $sender = $message->sender_role === 'admin' ? 'SHELL Support' : $ticket->guest_name;
            $lines[] = '['.$message->created_at.'] '.$sender.':';
            $lines[] = $message->message;
            $lines[] = '';
        }

        $body = implode("\n", $lines);
        $inbox = config('support.inbox_email');

        foreach (array_filter([$ticket->guest_email, $inbox]) as $recipient) {
            try {
                Mail::raw($body, function ($mail) use ($recipient, $subject, $ticket) {
                    $mail->to($recipient)->subject($subject.' · SHELL ticket #'.$ticket->id);
                });
            } catch (\Throwable $exception) {
                Log::error('Support conversation email could not be sent.', [
                    'ticket_id' => $ticketId,
                    'recipient' => $recipient,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if (!$inbox) {
            Log::warning('SUPPORT_INBOX_EMAIL is not configured; team email copy was skipped.', ['ticket_id' => $ticketId]);
        }
    }
}
