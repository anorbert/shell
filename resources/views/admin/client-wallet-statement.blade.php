@extends('layouts.app')
@section('title', 'Wallet statement')
@section('eyebrow', 'ADMINISTRATION / CLIENT ACCOUNTS / WALLET STATEMENT')
@section('heading', 'Wallet statement')
@section('content')
<style>
.wallet-statement{display:grid;gap:18px}.ws-hero{border:1px solid rgba(59,130,246,.28);background:linear-gradient(120deg,rgba(37,99,235,.12),rgba(15,23,42,.03))}
.ws-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;padding:0 20px 20px}.ws-summary article{padding:13px;border:1px solid rgba(148,163,184,.2);border-radius:11px;background:rgba(148,163,184,.045)}
.ws-summary small{display:block;color:var(--muted,#94a3b8);font-size:10px;font-weight:800;letter-spacing:.07em}.ws-summary strong{display:block;margin-top:7px;font-size:17px;font-variant-numeric:tabular-nums}
.ws-actions{display:flex;gap:8px;flex-wrap:wrap}.ws-credit{color:#34d399;font-weight:800}.ws-debit{color:#fb7185;font-weight:800}.ws-reference{max-width:240px;overflow-wrap:anywhere;color:var(--muted,#94a3b8);font-size:11px}
@media(max-width:650px){.ws-summary{grid-template-columns:1fr;padding:0 14px 14px}.ws-hero .panel-head{padding:16px}}@media print{.sidebar,.top-nav,nav,.ws-actions,.pagination{display:none!important}.panel{box-shadow:none!important;border:1px solid #ddd!important}body{background:#fff!important;color:#111!important}}
</style>
<div class="wallet-statement">
<section class="panel ws-hero"><div class="panel-head"><div><span class="eyebrow">CLIENT #{{ $user->id }} · {{ $user->name }}</span><h2>{{ $wallet->code }} <span class="muted">/ {{ $wallet->currency }}</span></h2><p class="muted">Account statement · Ledger account #{{ $wallet->id }}</p></div><div class="ws-actions"><button type="button" class="btn btn-secondary" onclick="window.print()">Print statement</button><a class="btn btn-secondary" href="{{ route('admin.clients.show', $user->id) }}">Back to client</a></div></div>
<div class="ws-summary"><article><small>CLIENT</small><strong>{{ $user->name }}</strong></article><article><small>WALLET</small><strong>{{ $wallet->code }}</strong></article><article><small>CURRENT LEDGER BALANCE</small><strong>{{ number_format((float) $wallet->balance, 2) }} {{ $wallet->currency }}</strong></article></div></section>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">ACCOUNT ACTIVITY</span><h2>Statement entries</h2><p class="muted">Showing {{ $entries->count() }} of {{ $entries->total() }} entries · Newest first</p></div></div>
<div class="table-wrap"><table class="data-table"><thead><tr><th>DATE</th><th>DESCRIPTION</th><th>DEBIT</th><th>CREDIT</th><th>REFERENCE</th></tr></thead><tbody>
@forelse($entries as $entry)<tr><td>{{ $entry->posted_at }}</td><td>{{ $entry->description }}</td><td class="ws-debit">{{ $entry->side === 'debit' ? number_format((float) $entry->amount, 2).' '.$wallet->currency : '—' }}</td><td class="ws-credit">{{ $entry->side === 'credit' ? number_format((float) $entry->amount, 2).' '.$wallet->currency : '—' }}</td><td><span class="ws-reference">{{ $entry->idempotency_key }}</span></td></tr>
@empty<tr><td colspan="5" class="table-empty">No entries exist for this wallet yet.</td></tr>@endforelse
</tbody></table></div><div style="padding:16px 20px">{{ $entries->links() }}</div></section></div>
@endsection
