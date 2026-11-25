<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\ManualWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class KycController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $kyc = DB::table('kyc_submissions')->where('user_id', $user->id)->latest()->first();
        $kycVerified = app(ManualWalletService::class)->hasApprovedKyc($user);

        return view('client.kyc.index', compact('kyc', 'kycVerified'));
    }

    public function submit(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'document_type' => ['required', 'in:national_id,passport,driving_licence'],
            'document_number' => ['required', 'string', 'min:4', 'max:120'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'selfie' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $existing = DB::table('kyc_submissions')->where('user_id', $user->id)->whereIn('status', ['pending', 'verified'])->exists();
        if ($existing) {
            return back()->withErrors(['kyc' => 'Your KYC is already pending or verified.']);
        }

        $document = $request->file('document');
        $documentPath = 'kyc/'.$user->id.'/'.Str::uuid().'.'.$document->getClientOriginalExtension().'.enc';
        Storage::disk('local')->put($documentPath, Crypt::encryptString(file_get_contents($document->getRealPath())));
        $selfie = $request->file('selfie');
        $selfiePath = 'kyc/'.$user->id.'/'.Str::uuid().'.'.$selfie->getClientOriginalExtension().'.enc';
        Storage::disk('local')->put($selfiePath, Crypt::encryptString(file_get_contents($selfie->getRealPath())));

        DB::table('kyc_submissions')->insert([
            'user_id' => $user->id,
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number'],
            'document_path' => $documentPath,
            'document_mime' => $document->getMimeType(),
            'selfie_path' => $selfiePath,
            'selfie_mime' => $selfie->getMimeType(),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(ManualWalletService::class)->notifyAdmins(
            'kyc-submission:'.$user->id.':'.now()->timestamp,
            'KYC submission requires review',
            $user->name.' ('.$user->email.') submitted KYC documents.',
            ['user_id' => $user->id]
        );

        return to_route('kyc.index')->with('status', 'KYC submitted. Withdrawals remain locked until an administrator verifies your documents.');
    }
}
