<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\LedgerService;
use App\Support\Decimal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DemoWalletController extends Controller
{
    public function topUp(Request $request, LedgerService $ledger): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'regex:/^(?:[1-9][0-9]{0,4})(?:\.[0-9]{1,8})?$/'],
            'request_key' => ['required', 'uuid'],
        ]);

        try {
            $currency = config('shell.paper.currency', 'USD');
            $ledger->addDemoFunds($request->user(), $validated['amount'], $validated['request_key'], $currency);
            $wallet = $ledger->demoWallet($request->user(), $currency);

            return response()->json([
                'message' => 'Demo funds added. These credits have no cash value and cannot be withdrawn.',
                'balance' => Decimal::fromUnits($ledger->balanceUnits($wallet)),
                'currency' => $currency,
            ], 201);
        } catch (RuntimeException|\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
