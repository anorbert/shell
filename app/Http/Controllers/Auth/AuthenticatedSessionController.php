<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request)
    {
        return view('auth.login', ['status' => $request->session()->get('status')]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        DB::table('user_login_events')->insert([
            'user_id' => $request->user()->id,
            'event_type' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 5000),
            'created_at' => now(),
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        if ($request->user()) {
            DB::table('user_login_events')->insert([
                'user_id' => $request->user()->id,
                'event_type' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 5000),
                'created_at' => now(),
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
