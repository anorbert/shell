<?php

namespace App\\Http\\Controllers\\Client\\Settings;

use App\\Http\\Controllers\\Controller;
use App\\Http\\Requests\\Settings\\ProfileUpdateRequest;
use App\\Services\\ManualWalletService;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;
use Illuminate\\Support\\Facades\\DB;

class ProfileController extends Controller
{
    public function edit(Request $request, ManualWalletService $wallets)
    {
        $user = $request->user();
        $kyc = DB::table('kyc_submissions')->where('user_id', $user->id)->latest()->first();
        $hasSuccessfulDeposit = $wallets->hasSuccessfulDeposit($user);
        $kycVerified = $wallets->hasApprovedKyc($user);

        return view('client.settings.profile', compact('kyc', 'hasSuccessfulDeposit', 'kycVerified'));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $user = $request->user();

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
