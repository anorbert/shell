<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $settings = DB::table('referral_settings')->where('id', 1)->first();
        $referrals = DB::table('users')
            ->leftJoin('wallet_requests', function ($join) {
                $join->on('wallet_requests.user_id', '=', 'users.id')
                    ->where('wallet_requests.type', '=', 'deposit')
                    ->where('wallet_requests.status', '=', 'confirmed');
            })
            ->where('users.referred_by_user_id', $user->id)
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->selectRaw('COUNT(wallet_requests.id) as successful_deposits')
            ->selectRaw('MIN(wallet_requests.created_at) as first_deposit_at')
            ->orderByDesc('users.created_at')->paginate(15, ['*'], 'referrals_page');

        $commissions = DB::table('referral_commissions')
            ->join('users as referred', 'referred.id', '=', 'referral_commissions.referred_user_id')
            ->where('referral_commissions.referrer_user_id', $user->id)
            ->select('referral_commissions.*', 'referred.name as referred_name')
            ->latest('referral_commissions.created_at')->paginate(15, ['*'], 'commissions_page');

        $totals = DB::table('referral_commissions')->where('referrer_user_id', $user->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'paid' THEN commission_amount_usd ELSE 0 END),0) as paid_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'pending' THEN commission_amount_usd ELSE 0 END),0) as pending_total")
            ->selectRaw('COUNT(*) as commission_count')->first();

        $referralUrl = route('referral.capture', ['code' => $user->referral_code]);
        return view('client.referrals', compact('settings', 'referrals', 'commissions', 'totals', 'referralUrl'));
    }

    public function capture(string $code)
    {
        $referrer = DB::table('users')->where('referral_code', strtoupper($code))->where('role', 'client')->first();
        if (!$referrer) return redirect()->route('register')->with('referral_error', 'That referral link is not valid.');
        session(['referral_code' => $referrer->referral_code]);
        return redirect()->route('register');
    }
}
