@extends('layouts.app')
@section('title', 'Trading operations')
@section('eyebrow', 'ADMINISTRATION / CONTROL ROOM')
@section('heading', 'Trading operations')
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Read-only operational monitoring</strong><p>Administrators can inspect quote health and settlement state here. Trade outcomes and posted ledger entries cannot be manually edited from this screen.</p></div><span class="mode-tag">MONITORING</span></div>
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">MARKET PROVIDER</span><h2>Quote freshness</h2></div><span class="muted">Freshness limit: {{ config('shell.market.max_age_seconds') }} seconds</span></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>PAIR</th><th>PRICE</th><th>PROVIDER</th><th>CAPTURED AT</th><th>HEALTH</th></tr></thead><tbody>
    @forelse($quotes as $quote)
        <tr><td><strong>{{ $quote->symbol }}</strong></td><td>{{ $quote->price }} USDT</td><td>{{ $quote->provider }}</td><td>{{ $quote->captured_at }}</td><td><span class="table-status {{ $quote->is_fresh ? 'verified' : 'pending' }}">{{ $quote->is_fresh ? 'FRESH' : 'STALE' }}</span></td></tr>
    @empty
        <tr><td colspan="5" class="table-empty">No market quotes have been captured yet.</td></tr>
    @endforelse
    </tbody></table></div>
</section>
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">SETTLEMENT QUEUE</span><h2>Open paper trades</h2></div></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>PAIR</th><th>DIRECTION</th><th>STAKE</th><th>OPENED</th><th>EXPIRES</th></tr></thead><tbody>
    @forelse($openTrades as $trade)
        <tr><td><strong>{{ $trade->client_name }}</strong><small>{{ $trade->client_email }}</small></td><td>{{ $trade->symbol }}</td><td>{{ $trade->direction }}</td><td>{{ $trade->stake }} USDT</td><td>{{ $trade->opened_at }}</td><td>{{ $trade->expires_at }}</td></tr>
    @empty
        <tr><td colspan="6" class="table-empty">There are no open paper trades.</td></tr>
    @endforelse
    </tbody></table></div><div class="pagination-wrap">{{ $openTrades->links() }}</div>
</section>
<section class="panel">
    <div class="panel-head"><div><span class="eyebrow">AUDIT VIEW</span><h2>Recent settlements</h2></div></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>CLIENT</th><th>PAIR</th><th>OPEN</th><th>CLOSE</th><th>STAKE</th><th>OUTCOME</th><th>SETTLED AT</th></tr></thead><tbody>
    @forelse($recentSettlements as $trade)
        <tr><td>{{ $trade->client_name }}</td><td>{{ $trade->symbol }}</td><td>{{ $trade->opening_price }}</td><td>{{ $trade->closing_price }}</td><td>{{ $trade->stake }} USDT</td><td><span class="table-status verified">{{ strtoupper($trade->status) }}</span></td><td>{{ $trade->settled_at }}</td></tr>
    @empty
        <tr><td colspan="7" class="table-empty">No settlements recorded yet.</td></tr>
    @endforelse
    </tbody></table></div>
</section>
@endsection
