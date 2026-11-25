@extends('layouts.app')
@section('title','Wallet operations')
@section('eyebrow','FINANCE CONTROL')
@section('heading','Wallet operations')
@section('content')
@php
    $money = fn ($value) => number_format((float) ($value ?? 0), 2, '.', ',');
@endphp
<style>
.wallet-ops{--wo-ink:var(--text,#e9edf7);--wo-muted:var(--muted,#929db2);--wo-line:rgba(148,163,184,.18);--wo-surface:rgba(255,255,255,.035);display:grid;gap:22px;color:var(--wo-ink)}
.wallet-ops *{box-sizing:border-box}
.wallet-ops .wo-alert{display:flex;align-items:flex-start;gap:13px;padding:16px 18px;border:1px solid var(--wo-line);border-radius:15px;background:var(--wo-surface)}
.wallet-ops .wo-alert strong{display:block;font-size:13px;letter-spacing:.02em}
.wallet-ops .wo-alert p{margin:5px 0 0;color:var(--wo-muted);font-size:12px;line-height:1.65}
.wallet-ops .wo-alert-icon{display:grid;place-items:center;flex:0 0 30px;width:30px;height:30px;border-radius:10px;background:rgba(201,145,10,.14);color:#e9bd58;font-weight:800}
.wallet-ops .wo-alert.success{border-color:rgba(34,197,94,.25)}.wallet-ops .wo-alert.error{border-color:rgba(239,68,68,.3)}
.wallet-ops .wo-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px}
.wallet-ops .wo-stat{padding:17px 18px;border:1px solid var(--wo-line);border-radius:16px;background:var(--wo-surface);min-width:0}
.wallet-ops .wo-stat-top{display:flex;align-items:center;justify-content:space-between;gap:8px;color:var(--wo-muted);font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase}
.wallet-ops .wo-stat-icon{display:grid;place-items:center;width:31px;height:31px;border-radius:10px;background:rgba(59,130,246,.12);color:#7eb4ff;font-size:15px}
.wallet-ops .wo-stat-value{display:block;margin-top:12px;font-size:25px;line-height:1.1;font-weight:750;letter-spacing:-.04em;font-variant-numeric:tabular-nums}
.wallet-ops .wo-stat-sub{display:block;margin-top:6px;color:var(--wo-muted);font-size:11px}
.wallet-ops .wo-panel{overflow:hidden;border:1px solid var(--wo-line);border-radius:18px;background:var(--wo-surface);min-width:0}
.wallet-ops .wo-panel-head{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:20px 22px;border-bottom:1px solid var(--wo-line)}
.wallet-ops .wo-panel-head h2{margin:4px 0 0;font-size:17px;letter-spacing:-.02em}
.wallet-ops .wo-kicker{color:var(--wo-muted);font-size:10px;font-weight:800;letter-spacing:.12em}
.wallet-ops .wo-panel-body{padding:20px 22px}
.wallet-ops .wo-muted{color:var(--wo-muted);font-size:12px;line-height:1.65}
.wallet-ops .wo-count{padding:6px 10px;border:1px solid var(--wo-line);border-radius:99px;color:var(--wo-muted);font-size:11px;white-space:nowrap}
.wallet-ops .wo-table-wrap{width:100%;overflow-x:auto}
.wallet-ops table{width:100%;min-width:1020px;border-collapse:separate;border-spacing:0}
.wallet-ops th{padding:12px 14px;text-align:left;background:rgba(148,163,184,.055);border-bottom:1px solid var(--wo-line);color:var(--wo-muted);font-size:9px;letter-spacing:.1em;text-transform:uppercase;white-space:nowrap}
.wallet-ops td{padding:16px 14px;vertical-align:top;border-bottom:1px solid var(--wo-line);font-size:12px}
.wallet-ops tbody tr:last-child td{border-bottom:0}
.wallet-ops tbody tr:hover{background:rgba(148,163,184,.035)}
.wallet-ops .wo-primary{display:block;font-weight:700;color:var(--wo-ink);overflow-wrap:anywhere}
.wallet-ops .wo-secondary{display:block;margin-top:5px;color:var(--wo-muted);font-size:11px;line-height:1.55;overflow-wrap:anywhere}
.wallet-ops .wo-amount{font-weight:750;font-size:14px;font-variant-numeric:tabular-nums;white-space:nowrap}
.wallet-ops .wo-status{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:99px;background:rgba(148,163,184,.1);color:#cbd5e1;font-size:9px;font-weight:800;letter-spacing:.06em}
.wallet-ops .wo-status:before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor}
.wallet-ops .wo-status.pending{background:rgba(245,158,11,.12);color:#f4c15e}
.wallet-ops .wo-status.verified{background:rgba(34,197,94,.12);color:#67d69a}
.wallet-ops .wo-status.rejected{background:rgba(239,68,68,.12);color:#f28b8b}
.wallet-ops .wo-form{display:grid;gap:10px;min-width:220px}
.wallet-ops .wo-form label,.wallet-ops .wo-method-form label{display:grid;gap:6px;color:var(--wo-muted);font-size:10px;font-weight:700}
.wallet-ops input:not([type=checkbox]),.wallet-ops textarea,.wallet-ops select{width:100%;min-width:0;border:1px solid var(--wo-line);border-radius:9px;background:rgba(8,18,42,.42);color:var(--wo-ink);padding:10px 11px;font:inherit;font-size:12px;outline:none}
.wallet-ops input:focus,.wallet-ops textarea:focus,.wallet-ops select:focus{border-color:rgba(59,130,246,.65);box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.wallet-ops textarea{min-height:72px;resize:vertical}
.wallet-ops .wo-check{display:flex!important;grid-template-columns:16px 1fr;align-items:start;gap:8px!important;line-height:1.5;color:var(--wo-muted);font-weight:500!important}
.wallet-ops .wo-check input{margin:2px 0 0;accent-color:#3b82f6}
.wallet-ops .wo-actions{display:grid;gap:10px}
.wallet-ops .wo-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;width:100%;min-height:38px;padding:9px 12px;border:1px solid transparent;border-radius:9px;font-size:11px;font-weight:750;text-decoration:none;cursor:pointer}
.wallet-ops .wo-btn-primary{background:linear-gradient(135deg,#2463b8,#1d4e91);color:#fff}
.wallet-ops .wo-btn-secondary{background:rgba(148,163,184,.09);border-color:var(--wo-line);color:var(--wo-ink)}
.wallet-ops .wo-btn-danger{background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.2);color:#f28b8b}
.wallet-ops .wo-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}
.wallet-ops .wo-full{grid-column:1/-1}
.wallet-ops .wo-method-list{display:grid;gap:14px}
.wallet-ops .wo-method{display:grid;grid-template-columns:minmax(150px,.8fr) minmax(220px,1.2fr) minmax(260px,1.5fr);gap:18px;align-items:start;padding:17px;border:1px solid var(--wo-line);border-radius:14px}
.wallet-ops .wo-method-title{font-size:14px;font-weight:800}
.wallet-ops .wo-address{padding:10px;border-radius:9px;background:rgba(8,18,42,.35);font-family:ui-monospace,monospace;font-size:11px;line-height:1.6;overflow-wrap:anywhere}
.wallet-ops .wo-empty{padding:30px!important;text-align:center;color:var(--wo-muted)}
.wallet-ops .wo-footnote{padding:13px 15px;border-radius:11px;background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.16);color:var(--wo-muted);font-size:11px;line-height:1.65}
.wallet-ops .wo-pagination{padding:15px 20px;border-top:1px solid var(--wo-line)}
.wallet-ops .wo-inline-form{display:grid;gap:9px}
@media(max-width:1100px){.wallet-ops .wo-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.wallet-ops .wo-method{grid-template-columns:1fr 1fr}.wallet-ops .wo-method-actions{grid-column:1/-1}}
@media(max-width:650px){.wallet-ops{gap:15px}.wallet-ops .wo-stats{gap:9px}.wallet-ops .wo-stat{padding:13px}.wallet-ops .wo-stat-value{font-size:21px}.wallet-ops .wo-panel-head,.wallet-ops .wo-panel-body{padding:16px}.wallet-ops .wo-method{grid-template-columns:1fr}.wallet-ops .wo-method-actions{grid-column:auto}.wallet-ops .wo-grid{grid-template-columns:1fr}.wallet-ops .wo-full{grid-column:auto}}
</style>

<div class="wallet-ops">
    <div class="wo-alert">
        <span class="wo-alert-icon">!</span>
        <div><strong>Real-money operations control</strong><p>Review deposits and withdrawals with a complete audit trail. A submitted transaction hash is evidence to investigate, not proof of payment. Verify the network, token contract, receiving address, amount and confirmations before approving any deposit.</p></div>
    </div>
    @if(session('status'))<div class="wo-alert success"><span class="wo-alert-icon">✓</span><div><strong>{{ session('status') }}</strong></div></div>@endif
    @if($errors->any())<div class="wo-alert error"><span class="wo-alert-icon">!</span><div><strong>Action not completed</strong><p>{{ $errors->first() }}</p></div></div>@endif

    <section class="wo-stats">
        <article class="wo-stat"><div class="wo-stat-top"><span>Pending requests</span><span class="wo-stat-icon">◷</span></div><strong class="wo-stat-value">{{ number_format((int) ($walletStats->pending_requests ?? 0)) }}</strong><span class="wo-stat-sub">Awaiting operations review</span></article>
        <article class="wo-stat"><div class="wo-stat-top"><span>Deposits to review</span><span class="wo-stat-icon">↓</span></div><strong class="wo-stat-value">{{ number_format((int) ($walletStats->pending_deposits ?? 0)) }}</strong><span class="wo-stat-sub">Incoming client transfers</span></article>
        <article class="wo-stat"><div class="wo-stat-top"><span>Withdrawals to review</span><span class="wo-stat-icon">↑</span></div><strong class="wo-stat-value">{{ number_format((int) ($walletStats->pending_withdrawals ?? 0)) }}</strong><span class="wo-stat-sub">Outgoing payout requests</span></article>
        <article class="wo-stat"><div class="wo-stat-top"><span>Confirmed deposits</span><span class="wo-stat-icon">✓</span></div><strong class="wo-stat-value">{{ $money($walletStats->confirmed_deposits_usd ?? 0) }} <small style="font-size:11px;color:var(--wo-muted)">USD</small></strong><span class="wo-stat-sub">Lifetime confirmed deposit volume</span></article>
    </section>

    <section class="wo-panel">
        <div class="wo-panel-head"><div><span class="wo-kicker">OPERATIONS WORKSPACE</span><h2>Deposit &amp; withdrawal queue</h2></div><span class="wo-count">{{ number_format($requests->total()) }} total records</span></div>
        <div class="wo-table-wrap"><table><thead><tr><th>CLIENT</th><th>REQUEST DETAILS</th><th>BLOCKCHAIN EVIDENCE</th><th>STATUS</th><th>ADMIN ACTION</th></tr></thead><tbody>
        @forelse($requests as $item)
            <tr>
                <td><span class="wo-primary">{{ $item->client_name }}</span><span class="wo-secondary">{{ $item->client_email }}</span><span class="wo-secondary">Request #{{ $item->id }} · {{ $item->created_at }}</span></td>
                <td><span class="wo-primary">{{ strtoupper($item->type) }}</span><span class="wo-amount">{{ $money($item->amount_usd) }} USD</span><span class="wo-secondary">{{ $item->asset ?: '—' }} / {{ $item->network ?: '—' }}</span><span class="wo-secondary">Token quantity: {{ $item->asset_amount ? rtrim(rtrim(number_format((float) $item->asset_amount, 8, '.', ''), '0'), '.') : '—' }} {{ $item->asset }}</span></td>
                <td><span class="wo-secondary">Receiving / payout address</span><span class="wo-primary" style="font-family:ui-monospace,monospace;font-size:10px">{{ $item->wallet_address ?: '—' }}</span><span class="wo-secondary">Transaction hash</span><span class="wo-primary" style="font-family:ui-monospace,monospace;font-size:10px">{{ $item->transaction_hash ?: 'Not submitted' }}</span>@if($item->wallet_memo)<span class="wo-secondary">Memo: {{ $item->wallet_memo }}</span>@endif</td>
                <td><span class="wo-status {{ in_array($item->status,['confirmed','paid']) ? 'verified' : ($item->status==='rejected' ? 'rejected' : 'pending') }}">{{ strtoupper($item->status) }}</span>@if($item->reviewed_at)<span class="wo-secondary">Reviewed {{ $item->reviewed_at }}</span>@endif</td>
                <td>
                    @if($item->status==='pending')
                        <div class="wo-actions">
                        @if($item->type==='deposit' && !filled($item->transaction_hash))
                            <div class="wo-footnote" style="border-color:rgba(239,68,68,.22);background:rgba(239,68,68,.07);color:var(--wo-ink)">
                                <strong style="display:block;color:#f28b8b;margin-bottom:5px">No blockchain transaction hash submitted</strong>
                                Manual approval will credit the wallet based on admin authorization. The system will generate an internal reference; it will not create or imply an on-chain transaction.
                            </div>
                            <form method="POST" action="{{ route('admin.wallet-operations.deposit.confirm',$item->id) }}" class="wo-form">@csrf
                                <input type="hidden" name="approval_mode" value="manual">
                                <label>Amount to credit (USD)<input name="amount_usd" type="number" min="0.01" max="99999999" step="0.01" value="{{ number_format((float) $item->amount_usd, 2, '.', '') }}" required></label>
                                <label>Manual approval reason (optional)<textarea name="admin_note" maxlength="2000" placeholder="Optional internal note for the audit trail."></textarea></label>
                                <button class="wo-btn wo-btn-primary" type="submit">✓ Manually approve &amp; credit wallet</button>
                            </form>
                        @elseif($item->type==='deposit')
                            <form method="POST" action="{{ route('admin.wallet-operations.deposit.confirm',$item->id) }}" class="wo-form">@csrf
                                <input type="hidden" name="approval_mode" value="blockchain">
                                <label>Verified amount (USD)<input name="amount_usd" type="number" min="0.01" max="99999999" step="0.01" value="{{ number_format((float) $item->amount_usd, 2, '.', '') }}" required></label>
                                <label>Admin note (optional)<textarea name="admin_note" maxlength="2000" placeholder="Optional verification or approval note."></textarea></label>
                                <button class="wo-btn wo-btn-primary" type="submit">✓ Approve &amp; credit wallet</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.wallet-operations.withdrawal.paid',$item->id) }}" class="wo-form">@csrf
                                <label>On-chain payout transaction hash<input name="transaction_hash" maxlength="255" required></label>
                                <label>Actual crypto amount sent<input name="settlement_amount" type="number" min="0.00000001" step="0.00000001" required></label>
                                <label>Fee (USD)<input name="fee_usd" type="number" min="0" step="0.01" value="0.00"></label>
                                <label>Admin settlement note<input name="admin_note" maxlength="2000" required></label>
                                <button class="wo-btn wo-btn-primary" type="submit">✓ Mark payout as paid</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.wallet-operations.reject',$item->id) }}" class="wo-form">@csrf<label>Reason for rejection<input name="admin_note" maxlength="2000" placeholder="Explain why this request is rejected" required></label><button class="wo-btn wo-btn-danger" type="submit">Reject request</button></form>
                        </div>
                    @else
                        <span class="wo-secondary">Review note</span><span class="wo-primary" style="font-weight:500">{{ $item->admin_note ?: 'No review note recorded.' }}</span>
                    @endif
                </td>
            </tr>
        @empty<tr><td colspan="5" class="wo-empty">No wallet requests found. New client deposit and withdrawal requests will appear here.</td></tr>@endforelse
        </tbody></table></div>
        <div class="wo-pagination">{{ $requests->links() }}</div>
    </section>

    <section class="wo-panel">
        <div class="wo-panel-head"><div><span class="wo-kicker">CRYPTO CONFIGURATION</span><h2>Deposit &amp; withdrawal networks</h2></div><span class="wo-count">{{ $methods->count() }} configured</span></div>
        <div class="wo-panel-body">
            <p class="wo-muted">Configure the exact asset/network pair and official receiving address. Confirm every address with your custody operator before enabling deposits. Existing requests retain their original address.</p>
            <form method="POST" action="{{ route('admin.wallet-operations.methods.store') }}" class="wo-grid wo-method-form" style="margin:18px 0 22px">@csrf
                <label>Asset<input name="asset" placeholder="USDT" maxlength="20" required></label><label>Network<input name="network" placeholder="TRC20" maxlength="40" required></label>
                <label class="wo-full">Receiving wallet address<input name="wallet_address" maxlength="255" required></label>
                <label>Memo label (optional)<input name="memo_label" placeholder="Memo / Tag"></label><label>Memo value (optional)<input name="memo_value"></label>
                <label class="wo-full">Client instructions<textarea name="instructions" maxlength="2000" placeholder="Network-specific transfer instructions"></textarea></label>
                <div class="wo-full"><button class="wo-btn wo-btn-primary" style="width:auto;min-width:190px" type="submit">＋ Add crypto network</button></div>
            </form>
            <div class="wo-method-list">
            @forelse($methods as $method)
                <article class="wo-method">
                    <div><div class="wo-method-title">{{ $method->asset }} <span style="color:var(--wo-muted)">/ {{ $method->network }}</span></div><span class="wo-secondary">Network ID #{{ $method->id }}</span><div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:10px"><span class="wo-status {{ $method->deposits_enabled?'verified':'pending' }}">DEPOSITS {{ $method->deposits_enabled?'ON':'OFF' }}</span><span class="wo-status {{ $method->withdrawals_enabled?'verified':'pending' }}">WITHDRAWALS {{ $method->withdrawals_enabled?'ON':'OFF' }}</span></div></div>
                    <div><span class="wo-secondary">Receiving address</span><div class="wo-address">{{ $method->wallet_address }}</div>@if($method->memo_label || $method->memo_value)<span class="wo-secondary">{{ $method->memo_label }}: {{ $method->memo_value }}</span>@endif</div>
                    <div class="wo-method-actions">
                        <form method="POST" action="{{ route('admin.wallet-operations.methods.update',$method->id) }}" class="wo-grid wo-method-form">@csrf
                            <label>Deposit availability<select name="deposits_enabled"><option value="1" {{ $method->deposits_enabled?'selected':'' }}>Enabled</option><option value="0" {{ !$method->deposits_enabled?'selected':'' }}>Disabled</option></select></label>
                            <label>Withdrawal availability<select name="withdrawals_enabled"><option value="1" {{ $method->withdrawals_enabled?'selected':'' }}>Enabled</option><option value="0" {{ !$method->withdrawals_enabled?'selected':'' }}>Disabled</option></select></label>
                            <div class="wo-full"><button class="wo-btn wo-btn-secondary" type="submit">Save availability</button></div>
                        </form>
                        <details style="margin-top:12px"><summary style="cursor:pointer;color:var(--wo-muted);font-size:11px">Edit address and network details</summary>
                            <form method="POST" action="{{ route('admin.wallet-operations.methods.store') }}" class="wo-form" style="margin-top:10px">@csrf<input type="hidden" name="id" value="{{ $method->id }}">
                                <label>Asset<input name="asset" value="{{ $method->asset }}" required></label><label>Network<input name="network" value="{{ $method->network }}" required></label><label>Wallet address<input name="wallet_address" value="{{ $method->wallet_address }}" required></label><label>Memo label<input name="memo_label" value="{{ $method->memo_label }}"></label><label>Memo value<input name="memo_value" value="{{ $method->memo_value }}"></label><label>Instructions<textarea name="instructions">{{ $method->instructions }}</textarea></label><button class="wo-btn wo-btn-secondary" type="submit">Update network details</button>
                            </form>
                        </details>
                    </div>
                </article>
            @empty<p class="wo-empty">No crypto networks configured yet.</p>@endforelse
            </div>
        </div>
    </section>

    <section class="wo-panel">
        <div class="wo-panel-head"><div><span class="wo-kicker">IDENTITY &amp; COMPLIANCE</span><h2>KYC case management</h2></div><a class="wo-btn wo-btn-secondary" style="width:auto" href="{{ route('admin.kyc.index') }}">Open KYC workspace →</a></div>
        <div class="wo-panel-body"><p class="wo-muted">Review identity documents in the dedicated case workspace, including secure document previews, submission history and the approval checklist.</p></div>
    </section>

    <section class="wo-panel">
        <div class="wo-panel-head"><div><span class="wo-kicker">TREASURY RISK CONTROL</span><h2>Funded payout reserve</h2></div><span class="wo-status {{ (float) $realPayoutReserve > 0 ? 'verified' : 'pending' }}">{{ (float) $realPayoutReserve > 0 ? 'FUNDED' : 'NOT FUNDED' }}</span></div>
        <div class="wo-panel-body">
            <div class="wo-stat" style="margin-bottom:16px"><span class="wo-stat-top">Available payout reserve</span><strong class="wo-stat-value">{{ $money($realPayoutReserve) }} <small style="font-size:11px;color:var(--wo-muted)">USD</small></strong><span class="wo-stat-sub">Ledger balance; reconcile against segregated external treasury funds.</span></div>
            <div class="wo-footnote">This form records an accounting balance only. It does not transfer funds, verify blockchain custody or prove that external funds exist. Record only independently verified, segregated treasury funds and retain the external custody reference.</div>
            <form method="POST" action="{{ route('admin.wallet-operations.reserve.fund') }}" class="wo-grid wo-method-form" style="margin-top:18px">@csrf
                <label>Verified amount (USD)<input name="amount_usd" type="number" min="0.01" max="99999999" step="0.01" required></label>
                <label>External custody / bank reference<input name="external_reference" minlength="12" maxlength="255" required></label>
                <label class="wo-full">Verification and reconciliation note<textarea name="admin_note" minlength="10" maxlength="2000" required></textarea></label>
                <div class="wo-full"><button class="wo-btn wo-btn-primary" style="width:auto;min-width:220px" type="submit">Record verified reserve funding</button></div>
            </form>
        </div>
    </section>
</div>
@endsection
