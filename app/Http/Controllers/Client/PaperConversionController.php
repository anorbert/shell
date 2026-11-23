<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\LedgerService;
use App\Services\PaperConversionService;
use App\Support\Decimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use InvalidArgumentException;

class PaperConversionController extends Controller
{
    public function index(Request $request, PaperConversionService $service, LedgerService $ledger)
    {
        $currency = config('shell.paper.currency', 'USD');
        $ledger->provisionClientWallets($request->user(), $currency);

        $accounts = DB::table('ledger_accounts')
            ->where('owner_key', 'USER:' . $request->user()->id)
            ->where('code', 'PAPER_WALLET')
            ->get();

        $balances = [];
        foreach ($accounts as $account) {
            $balances[$account->currency] = Decimal::fromUnits($ledger->balanceUnits((int) $account->id));
        }

        $conversions = DB::table('paper_conversions')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(25)
            ->get();

        return view('client.convert', [
            'currency' => $currency,
            'currencies' => $service->currencies(),
            'balances' => $balances,
            'conversions' => $conversions,
        ]);
    }

    public function quote(Request $request, PaperConversionService $service)
    {
        $values = $request->validate([
            'from_currency' => ['required', 'string', 'max:12'],
            'to_currency' => ['required', 'string', 'max:12'],
            'amount' => ['required', 'regex:/^(0|[1-9][0-9]{0,9})(\.[0-9]{1,8})?$/'],
        ]);

        try {
            return response()->json($service->quote(
                strtoupper($values['from_currency']),
                strtoupper($values['to_currency']),
                $values['amount']
            ));
        } catch (RuntimeException | InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function convert(Request $request, PaperConversionService $service)
    {
        $values = $request->validate([
            'from_currency' => ['required', 'string', 'max:12'],
            'to_currency' => ['required', 'string', 'max:12'],
            'amount' => ['required', 'regex:/^(0|[1-9][0-9]{0,9})(\.[0-9]{1,8})?$/'],
            'source_quote_at' => ['nullable', 'string', 'max:40'],
            'target_quote_at' => ['nullable', 'string', 'max:40'],
            'request_key' => ['required', 'uuid'],
        ]);

        try {
            $row = $service->convert(
                $request->user(),
                strtoupper($values['from_currency']),
                strtoupper($values['to_currency']),
                $values['amount'],
                $values['source_quote_at'] ?? '',
                $values['target_quote_at'] ?? null,
                $values['request_key']
            );

            return response()->json(['message' => 'Paper conversion completed.', 'conversion' => $row], 201);
        } catch (RuntimeException | InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
