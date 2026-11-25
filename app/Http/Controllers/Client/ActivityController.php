<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityController extends Controller
{
    private function query(Request $request)
    {
        $values = $request->validate([
            'status' => ['nullable', 'in:open,won,lost,void'],
            'account' => ['nullable', 'in:demo,real'],
            'symbol' => ['nullable', 'string', 'max:24'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return DB::table('paper_trades')
            ->where('user_id', $request->user()->id)
            ->where('account_type', $request->session()->get('active_wallet', 'demo'))
            ->when($values['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($values['symbol'] ?? null, fn ($query, $value) => $query->where('symbol', strtoupper($value)))
            ->when($values['from'] ?? null, fn ($query, $value) => $query->whereDate('opened_at', '>=', $value))
            ->when($values['to'] ?? null, fn ($query, $value) => $query->whereDate('opened_at', '<=', $value))
            ->orderByDesc('opened_at');
    }

    public function __invoke(Request $request, \App\Services\PaperTradingService $settlement)
    {
        // Catch up expired positions in both account environments when the user opens trade activity.
        $settlement->settleDue();

        $trades = $this->query($request)->paginate(20)->withQueryString();

        return view('client.activity', compact('trades'));
    }

    public function export(Request $request): StreamedResponse
    {
        $trades = $this->query($request)->get();

        return response()->streamDownload(function () use ($trades) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Account', 'Opened at', 'Pair', 'Direction', 'Stake', 'Wallet currency', 'Opening price', 'Closing price', 'Status', 'Expires at', 'Settled at']);

            foreach ($trades as $trade) {
                fputcsv($output, [
                    $trade->account_type ?? 'demo',
                    $trade->opened_at,
                    $trade->symbol,
                    $trade->direction,
                    $trade->stake,
                    $trade->wallet_currency ?? config('shell.paper.currency', 'USD'),
                    $trade->opening_price,
                    $trade->closing_price,
                    $trade->status,
                    $trade->expires_at,
                    $trade->settled_at,
                ]);
            }

            fclose($output);
        }, 'shell-trading-activity.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
