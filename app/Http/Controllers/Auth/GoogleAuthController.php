<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (!config('services.google.client_id') || !config('services.google.client_secret')) {
            return to_route('login')->withErrors([
                'google' => 'Google sign-in is not configured yet. Please use your email and password.',
            ]);
        }

        $state = Str::random(48);
        $request->session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function callback(Request $request, LedgerService $ledger): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('google_oauth_state', '');
        $receivedState = (string) $request->query('state', '');

        if ($expectedState === '' || $receivedState === '' || !hash_equals($expectedState, $receivedState)) {
            return to_route('login')->withErrors([
                'google' => 'The Google sign-in session expired or could not be verified. Please try again.',
            ]);
        }

        if ($request->filled('error') || !$request->filled('code')) {
            return to_route('login')->withErrors([
                'google' => 'Google sign-in was cancelled or could not be completed.',
            ]);
        }

        try {
            $tokenResponse = Http::asForm()->acceptJson()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'code' => $request->query('code'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => config('services.google.redirect'),
            ])->throw()->json();

            $accessToken = $tokenResponse['access_token'] ?? null;
            if (!$accessToken) {
                throw new \RuntimeException('Google did not return an access token.');
            }

            $google = Http::withToken($accessToken)->acceptJson()->timeout(15)
                ->get('https://www.googleapis.com/oauth2/v3/userinfo')->throw()->json();

            $googleId = (string) ($google['sub'] ?? '');
            $email = mb_strtolower(trim((string) ($google['email'] ?? '')));
            $emailVerified = filter_var($google['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if ($googleId === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$emailVerified) {
                return to_route('login')->withErrors([
                    'google' => 'Google must provide a verified email address to use this sign-in method.',
                ]);
            }

            $user = DB::transaction(function () use ($googleId, $email, $google, $ledger, $request) {
                $user = User::where('google_id', $googleId)->lockForUpdate()->first();

                if (!$user) {
                    // A verified Google email can securely connect to an existing Shell account.
                    $user = User::where('email', $email)->lockForUpdate()->first();
                }

                if ($user) {
                    if ($user->google_id && !hash_equals((string) $user->google_id, $googleId)) {
                        throw new \RuntimeException('This Shell account is already connected to another Google account.');
                    }

                    $user->google_id = $googleId;
                    $user->email_verified_at ??= now();
                    $user->save();

                    return $user;
                }

                $user = User::create([
                    'name' => trim((string) ($google['name'] ?? '')) ?: Str::before($email, '@'),
                    'email' => $email,
                    'password' => Str::random(64),
                ]);

                $user->google_id = $googleId;
                $user->email_verified_at = now();
                $user->save();

                do {
                    $referralCode = strtoupper(Str::random(10));
                } while (DB::table('users')->where('referral_code', $referralCode)->exists());

                $updates = ['referral_code' => $referralCode];
                $incomingCode = strtoupper(trim((string) $request->session()->pull('referral_code', '')));
                if ($incomingCode !== '') {
                    $referrerId = DB::table('users')->where('referral_code', $incomingCode)
                        ->where('role', 'client')->value('id');
                    if ($referrerId && (int) $referrerId !== (int) $user->id) {
                        $updates['referred_by_user_id'] = $referrerId;
                    }
                }

                DB::table('users')->where('id', $user->id)->update($updates);
                $ledger->provisionClientWallets($user, config('shell.paper.currency', 'USD'));

                return $user;
            }, 3);

            Auth::login($user);
            $request->session()->regenerate();

            DB::table('user_login_events')->insert([
                'user_id' => $user->id,
                'event_type' => 'login',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 5000),
                'created_at' => now(),
            ]);

            return to_route('dashboard');
        } catch (Throwable $exception) {
            report($exception);

            return to_route('login')->withErrors([
                'google' => 'We could not complete Google sign-in. Please try again or use your email and password.',
            ]);
        }
    }
}
