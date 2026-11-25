<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        abort_unless(!$status || in_array($status, ['open', 'pending', 'closed'], true), 422);

        $tickets = DB::table('support_tickets')
            ->leftJoin('users', 'users.id', '=', 'support_tickets.user_id')
            ->select(
                'support_tickets.*',
                DB::raw('COALESCE(support_tickets.guest_name, users.name) as client_name'),
                DB::raw('COALESCE(support_tickets.guest_email, users.email) as client_email')
            )
            ->when($status, fn ($query) => $query->where('support_tickets.status', $status))
            ->latest('support_tickets.last_message_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.support.index', compact('tickets'));
    }

    public function show(int $ticket)
    {
        $record = DB::table('support_tickets')
            ->leftJoin('users', 'users.id', '=', 'support_tickets.user_id')
            ->select(
                'support_tickets.*',
                DB::raw('COALESCE(support_tickets.guest_name, users.name) as client_name'),
                DB::raw('COALESCE(support_tickets.guest_email, users.email) as client_email')
            )
            ->where('support_tickets.id', $ticket)
            ->first();

        abort_unless($record, 404);

        $messages = DB::table('support_messages')
            ->leftJoin('users', 'users.id', '=', 'support_messages.user_id')
            ->where('support_ticket_id', $record->id)
            ->select('support_messages.*', 'users.name as sender_name')
            ->orderBy('support_messages.created_at')
            ->get();

        return view('admin.support.show', ['ticket' => $record, 'messages' => $messages]);
    }

    public function reply(Request $request, int $ticket)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:5000'],
            'status' => ['required', 'in:open,pending,closed'],
        ]);

        $record = DB::table('support_tickets')->where('id', $ticket)->first();
        abort_unless($record, 404);

        DB::transaction(function () use ($request, $data, $record) {
            $now = now();
            DB::table('support_messages')->insert([
                'support_ticket_id' => $record->id,
                'user_id' => $request->user()->id,
                'sender_role' => 'admin',
                'message' => trim($data['message']),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('support_tickets')->where('id', $record->id)->update([
                'status' => $data['status'],
                'closed_at' => $data['status'] === 'closed' ? $now : null,
                'last_message_at' => $now,
                'updated_at' => $now,
            ]);

            if ($record->user_id) {
                $messageId = DB::table('support_messages')->where('support_ticket_id', $record->id)->max('id');
                DB::table('user_notifications')->insertOrIgnore([
                    'user_id' => $record->user_id,
                    'event_key' => 'support-reply:'.$record->id.':'.$messageId,
                    'type' => 'support_reply',
                    'title' => 'Support replied to your ticket',
                    'body' => 'An administrator replied to support ticket #'.$record->id.'.',
                    'data' => json_encode(['ticket_id' => $record->id]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        if ($record->guest_email) {
            $this->emailGuestTranscript($record->id);
        }

        return redirect()->route('admin.support.show', $record->id)->with('status', 'Support reply sent.');
    }

    private function emailGuestTranscript(int $ticketId): void
    {
        $ticket = DB::table('support_tickets')->where('id', $ticketId)->first();
        if (!$ticket || !$ticket->guest_email) {
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

        $recipients = array_filter([$ticket->guest_email, config('support.inbox_email')]);
        foreach (array_unique($recipients) as $recipient) {
            try {
                Mail::raw(implode("\n", $lines), function ($mail) use ($recipient, $ticketId) {
                    $mail->to($recipient)->subject('SHELL support conversation update · ticket #'.$ticketId);
                });
            } catch (\Throwable $exception) {
                Log::error('Guest support reply email could not be sent.', [
                    'ticket_id' => $ticketId,
                    'recipient' => $recipient,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}
