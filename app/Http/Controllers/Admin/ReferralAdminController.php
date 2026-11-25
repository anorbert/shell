<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReferralCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReferralAdminController extends Controller
{
    public function index()
    {
        $settings = DB::table('referral_settings')->where('id', 1)->first();
        $commissions = DB::table('referral_commissions')
            ->join('users as referrers', 'referrers.id', '=', 'referral_commissions.referrer_user_id')
            ->join('users as referred', 'referred.id', '=', 'referral_commissions.referred_user_id')
            ->leftJoin('wallet_requests', 'wallet_requests.id', '=', 'referral_commissions.wallet_request_id')
            ->select('referral_commissions.*', 'referrers.name as referrer_name', 'referrers.email as referrer_email',
                'referred.name as referred_name', 'referred.email as referred_email',
                'wallet_requests.status as deposit_status')
            ->latest('referral_commissions.created_at')->paginate(25);
        $stats = DB::table('referral_commissions')
            ->selectRaw("COUNT(*) as total, SUM(status = 'pending') as pending_count, COALESCE(SUM(CASE WHEN status = 'paid' THEN commission_amount_usd ELSE 0 END),0) as paid_total")
            ->first();
        return view('admin.referrals', compact('settings', 'commissions', 'stats'));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'first_deposit_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'recurring_deposit_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'minimum_deposit_usd' => ['required', 'numeric', 'min:0', 'max:100000000'],
        ]);
        DB::table('referral_settings')->where('id', 1)->update($data + ['updated_at' => now()]);
        return back()->with('status', 'Referral program settings saved. New qualifying deposits will use these rates.');
    }

    public function approve(Request $request, int $commission, ReferralCommissionService $service)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:2000']]);
        try {
            $service->approve($commission, $request->user(), $data['admin_note'] ?? null);
            return back()->with('status', 'Referral commission approved and credited to the referrer real wallet.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['commission' => $exception->getMessage()]);
        }
    }

    public function reject(Request $request, int $commission, ReferralCommissionService $service)
    {
        $data = $request->validate(['admin_note' => ['required', 'string', 'min:3', 'max:2000']]);
        try {
            $service->reject($commission, $request->user(), $data['admin_note']);
            return back()->with('status', 'Referral commission rejected.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['commission' => $exception->getMessage()]);
        }
    }
}
