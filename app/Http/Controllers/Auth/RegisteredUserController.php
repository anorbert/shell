<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    public function create(Request $request)
    {
        $code = strtoupper(trim((string) $request->query('ref', '')));
        if ($code !== '' && DB::table('users')->where('referral_code', $code)->where('role', 'client')->exists()) {
            $request->session()->put('referral_code', $code);
        }
        return view('auth.register');
    }

    public function store(Request $request, LedgerService $ledger): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'country_code' => ['required', 'string', 'size:2', Rule::in(array_keys(config('phone-countries', [])))],
            'phone_number' => ['required', 'string', 'regex:/^[+0-9().\s-]{6,25}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $dialCode = config('phone-countries.'.$validated['country_code']);
        $rawPhone = trim($validated['phone_number']);
        $nationalNumber = preg_replace('/\D+/', '', $rawPhone);
        $dialDigits = preg_replace('/\D+/', '', $dialCode);

        // Accept either a national number or a complete + / 00 international number.
        if (str_starts_with($rawPhone, '+') || str_starts_with($rawPhone, '00')) {
            if (str_starts_with($rawPhone, '00')) {
                $nationalNumber = substr($nationalNumber, 2);
            }
            if (!str_starts_with($nationalNumber, $dialDigits)) {
                return back()->withErrors([
                    'phone_number' => 'The phone country calling code does not match the selected country.',
                ])->withInput();
            }
            $nationalNumber = substr($nationalNumber, strlen($dialDigits));
        } else {
            // Remove the domestic trunk prefix before storing the international E.164 form.
            $nationalNumber = ltrim($nationalNumber, '0');
        }

        $phoneE164 = $dialCode.$nationalNumber;
        $phoneDigits = preg_replace('/\D+/', '', $phoneE164);

        if (strlen($phoneDigits) < 7 || strlen($phoneDigits) > 15) {
            return back()->withErrors([
                'phone_number' => 'Enter a valid phone number for the selected country (7–15 digits including country code).',
            ])->withInput();
        }

        $referralCode = $request->session()->pull('referral_code');
        $referrerId = $referralCode ? DB::table('users')->where('referral_code', $referralCode)->where('role', 'client')->value('id') : null;

        $user = DB::transaction(function () use ($validated, $phoneE164, $ledger, $referrerId) {
            do {
                $newReferralCode = strtoupper(Str::random(10));
            } while (DB::table('users')->where('referral_code', $newReferralCode)->exists());

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'country_code' => $validated['country_code'],
                'phone_e164' => $phoneE164,
                'password' => $validated['password'],
            ]);

            DB::table('users')->where('id', $user->id)->update(['referral_code' => $newReferralCode]);

            if ($referrerId && (int) $referrerId !== (int) $user->id) {
                DB::table('users')->where('id', $user->id)->update(['referred_by_user_id' => $referrerId]);
            }

            // Provision both independent wallets atomically. No real funds are created.
            $ledger->provisionClientWallets($user, config('shell.paper.currency', 'USD'));

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return to_route('dashboard');
    }
}
