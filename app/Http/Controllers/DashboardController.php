<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'admin'], true), 403);

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('client.dashboard', [
            'user' => $user,
            'accountStatus' => 'paper',
        ]);
    }
}
