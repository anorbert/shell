<?php

namespace App\\Http\\Controllers\\Client;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $notifications = DB::table('user_notifications')
            ->where('user_id', $userId)
            ->latest()
            ->paginate(20);

        $totalCount = DB::table('user_notifications')
            ->where('user_id', $userId)
            ->count();

        $unreadCount = DB::table('user_notifications')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        return view('client.notifications', compact(
            'notifications',
            'totalCount',
            'unreadCount'
        ));
    }

    public function markRead(Request $request, int $notification)
    {
        $query = DB::table('user_notifications')
            ->where('id', $notification)
            ->where('user_id', $request->user()->id);

        abort_unless($query->exists(), 404);

        $query->whereNull('read_at')->update([
            'read_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['read' => true]);
    }
}
