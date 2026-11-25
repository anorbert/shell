@extends('layouts.app')
@section('title','Wallet')
@section('eyebrow','WALLET CENTER')
@section('heading','My wallet')
@section('content')
<style>
.wallet-page{--wallet-accent:#69b8e6}.wallet-page .wallet-account-switch{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-bottom:18px}
.wallet-page .wallet-account-option{position:relative;display:flex;flex-direction:column;gap:7px;padding:18px 20px;border:1px solid var(--line,#26364a);border-radius:16px;background:var(--panel,#101b2b);text-decoration:none;transition:border-color .2s,transform .2s,background .2s}
.wallet-page .wallet-account-option:hover{transform:translateY(-2px);border-color:#5386aa}
.wallet-page .wallet-account-option.active{border-color:#4e9dcc;background:linear-gradient(135deg,rgba(40,115,159,.19),rgba(16,27,43,.98));box-shadow:inset 0 0 0 1px rgba(89,170,216,.12)}
.wallet-page .wallet-account-name{display:flex;align-items:center;gap:8px;color:var(--muted,#91a0b5);font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
.wallet-page .wallet-account-name:before{content:"";width:8px;height:8px;border-radius:50%;background:#7891a8}
.wallet-page .wallet-account-option.active .wallet-account-name:before{background:#6ed5a4;box-shadow:0 0 0 4px rgba(110,213,164,.1)}
.wallet-page .wallet-account-option>strong{font-size:clamp(21px,3vw,28px);font-variant-numeric:tabular-nums}
.wallet-page .wallet-account-option>strong small{font-size:12px;color:var(--muted,#91a0b5)}
.wallet-page .wallet-account-hint{font-size:12px;color:var(--muted,#91a0b5)}
.wallet-page .page-action-row{display:flex;flex-wrap:wrap;gap:10px;margin:14px 0 18px}
.wallet-page .button svg{width:17px;height:17px;vertical-align:middle;margin-right:7px}
.wallet-page .metric-grid{gap:14px}
.wallet-page .metric-card,.wallet-page .panel{border-radius:15px}
.wallet-page .metric-card{position:relative;overflow:hidden;padding:19px;border:1px solid var(--line,#26364a)}
.wallet-page .metric-card:before{content:"";position:absolute;right:17px;top:17px;width:34px;height:34px;border-radius:11px;background:rgba(91,170,215,.12)}
.wallet-page .metric-card:after{content:"$";position:absolute;right:28px;top:22px;color:#78bfe5;font-size:17px;font-weight:800}
.wallet-page .metric-card .metric-label{font-size:10px;letter-spacing:.09em}
.wallet-page .metric-card>strong{font-size:clamp(22px,3vw,29px);font-variant-numeric:tabular-nums}
.wallet-page .panel{overflow:hidden}.wallet-page #deposit-form,.wallet-page #demo-topup-form{scroll-margin-top:24px}
.wallet-page .panel-head{padding:18px 20px;border-bottom:1px solid var(--line,#26364a);margin-bottom:16px}
.wallet-page .panel-head h2{font-size:17px;margin-top:3px}
.wallet-page .form-stack{gap:14px}
.wallet-page .form-stack label{font-size:12px;font-weight:700;color:var(--muted,#a1afc0)}
.wallet-page .form-stack input,.wallet-page .form-stack select,.wallet-page .form-stack textarea{margin-top:7px;border-radius:10px}
.wallet-page .form-stack .button{min-height:44px}
.wallet-page .muted{line-height:1.65}
.wallet-page .content-grid{gap:16px;align-items:start}
.wallet-page .notice-banner{border-radius:12px}
.wallet-page .table-wrap{overflow-x:auto}
.wallet-page .data-table{min-width:760px}
.wallet-page .data-table th{font-size:10px;letter-spacing:.07em}
.wallet-page .data-table td{font-size:12px}
.wallet-page .table-status{white-space:nowrap}
.wallet-page .wallet-section-icon{display:inline-grid;place-items:center;width:34px;height:34px;margin-right:9px;border-radius:10px;background:rgba(79,151,197,.13);color:#78bfe5;vertical-align:middle}
.wallet-page .wallet-section-icon svg{width:18px;height:18px}
.wallet-page .wallet-empty{padding:25px!important;text-align:center;color:var(--muted,#91a0b5)}
.wallet-page .wallet-empty svg{display:block;width:28px;height:28px;margin:0 auto 8px;color:#78bfe5}
/* Keep every wallet section on the same alignment grid. */
.wallet-page .wallet-detail-metrics{grid-template-columns:repeat(2,minmax(0,1fr));align-items:stretch}
.wallet-page .metric-card{min-width:0;min-height:132px}
.wallet-page .metric-card:after{content:none}
.wallet-page .content-grid{grid-template-columns:repeat(2,minmax(0,1fr));align-items:stretch;gap:18px}
.wallet-page .content-grid>.panel{display:flex;flex-direction:column;min-width:0;height:100%;padding:0}
.wallet-page .panel{padding:0}
.wallet-page .panel>.panel-head{display:flex;align-items:center;justify-content:space-between;min-height:76px;margin:0;padding:17px 20px;border-bottom:1px solid var(--line,#26364a)}
.wallet-page .panel-head>div{display:grid;grid-template-columns:36px minmax(0,1fr);grid-template-rows:auto auto;align-items:center;column-gap:10px;min-width:0}
.wallet-page .panel-head .wallet-section-icon{grid-column:1;grid-row:1/3;margin:0}
.wallet-page .panel-head .eyebrow{grid-column:2;grid-row:1;min-width:0}
.wallet-page .panel-head h2{grid-column:2;grid-row:2;margin:3px 0 0;line-height:1.3}
.wallet-page .panel>.muted{margin:18px 20px 0}
.wallet-page .panel>.form-stack{width:auto;margin:18px 20px 20px;gap:15px}
.wallet-page .panel>.page-action-row{margin:16px 20px 20px}
.wallet-page .panel>.table-wrap{width:100%;min-width:0}
.wallet-page .panel>.pagination-wrap{margin:0 20px 18px}
.wallet-page .panel .form-stack label{display:grid;gap:7px;min-width:0}
.wallet-page .panel .form-stack input,.wallet-page .panel .form-stack select,.wallet-page .panel .form-stack textarea{box-sizing:border-box;width:100%;min-width:0;min-height:44px;margin:0;padding:11px 12px;border:1px solid #35445a;border-radius:10px;background:#0b1421;color:#f1f4f8}
.wallet-page .panel .form-stack textarea{min-height:88px;resize:vertical}
.wallet-page .panel .form-stack input:focus,.wallet-page .panel .form-stack select:focus,.wallet-page .panel .form-stack textarea:focus{outline:0;border-color:#69b8e6;box-shadow:0 0 0 3px rgba(105,184,230,.12)}
.wallet-page .panel .form-stack .notice-banner{align-items:flex-start;min-width:0;margin:0;overflow-wrap:anywhere}
.wallet-page .panel .form-stack .notice-banner>div{min-width:0;flex:1}
.wallet-page .panel .form-stack .notice-banner button{flex:0 0 auto}
.wallet-page .panel>.muted+.form-stack{margin-top:16px}
.wallet-page .panel>.muted+.button{align-self:flex-start;margin:18px 20px 20px}
.wallet-page .panel .form-stack>.muted{margin:0}
.wallet-page .panel>.table-wrap .data-table{min-width:850px}
.wallet-page .table-wrap{width:100%;max-width:100%}
.wallet-page .wallet-account-option{min-width:0}
.wallet-page .wallet-account-option>strong{overflow-wrap:anywhere}
.wallet-page .page-action-row{align-items:center}
@media(max-width:900px){.wallet-page .content-grid{grid-template-columns:1fr}.wallet-page .content-grid>.panel{height:auto}}
@media(max-width:700px){.wallet-page .wallet-account-switch{grid-template-columns:1fr}.wallet-page .wallet-account-option{padding:16px}.wallet-page .wallet-detail-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.wallet-page .panel>.panel-head{padding:15px 16px}.wallet-page .panel>.muted{margin-left:16px;margin-right:16px}.wallet-page .panel>.form-stack{margin-left:16px;margin-right:16px}.wallet-page .panel>.page-action-row{margin-left:16px;margin-right:16px}.wallet-page .panel>.muted+.button{margin-left:16px}.wallet-page .metric-card{padding:16px;min-height:120px}}
@media(max-width:440px){.wallet-page .wallet-detail-metrics{grid-template-columns:1fr}.wallet-page .page-action-row{align-items:stretch}.wallet-page .page-action-row .button{width:100%}.wallet-page .panel>.panel-head{gap:8px}.wallet-page .panel-head h2{font-size:15px}}

/* Wallet workspace refinement: consistent spacing, hierarchy and responsive alignment. */
.wallet-page{width:100%;max-width:100%;min-width:0;color:var(--text,#eef3f8)}
.wallet-page>*{min-width:0}
.wallet-page .wallet-account-switch{grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin:0 0 18px}
.wallet-page .wallet-account-option{min-height:142px;padding:20px 22px;justify-content:space-between;border-radius:18px;background:linear-gradient(145deg,rgba(20,34,52,.96),rgba(12,22,36,.98));box-shadow:0 8px 24px rgba(0,0,0,.12)}
.wallet-page .wallet-account-option.active{border-color:rgba(105,184,230,.72);background:linear-gradient(145deg,rgba(35,91,126,.30),rgba(12,22,36,.98));box-shadow:inset 0 0 0 1px rgba(105,184,230,.12),0 10px 26px rgba(0,0,0,.16)}
.wallet-page .wallet-account-name{display:flex;align-items:center;gap:10px;font-size:11px;letter-spacing:.1em}
.wallet-page .wallet-account-name .wallet-section-icon{width:32px;height:32px;margin:0;border-radius:10px}
.wallet-page .wallet-account-option>strong{display:block;margin:8px 0 2px;font-size:clamp(25px,3vw,32px);line-height:1.15;letter-spacing:-.035em}
.wallet-page .wallet-account-option>strong small{font-size:12px;font-weight:700;letter-spacing:0}
.wallet-page .wallet-account-hint{display:block;line-height:1.5}
.wallet-page .page-action-row{display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-start;gap:10px;margin:0 0 20px}
.wallet-page .page-action-row>.button{box-sizing:border-box;display:inline-flex;flex:0 0 auto;align-items:center;justify-content:center;gap:9px;min-height:44px;padding:0 16px;line-height:1.2;white-space:nowrap;text-align:center}
.wallet-page .page-action-row>.button svg{display:block;flex:0 0 17px;width:17px;height:17px;margin:0;vertical-align:initial}
.wallet-page .page-action-row .button{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:10px 16px;border-radius:10px;font-weight:750}
.wallet-page .notice-banner{margin:0 0 18px;padding:14px 16px;line-height:1.55}
.wallet-page .metric-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin:0 0 20px}
.wallet-page .metric-card{display:flex;flex-direction:column;justify-content:center;gap:7px;min-height:126px;padding:20px 22px;border-radius:16px;background:linear-gradient(145deg,rgba(19,33,51,.98),rgba(12,21,34,.98));box-shadow:0 8px 24px rgba(0,0,0,.10)}
.wallet-page .metric-card .metric-label{color:var(--muted,#91a0b5);font-size:10px;font-weight:800;letter-spacing:.11em}
.wallet-page .metric-card>strong{display:block;margin:0;font-size:clamp(24px,3vw,31px);line-height:1.15;letter-spacing:-.03em}
.wallet-page .metric-card>small{font-size:11px;line-height:1.45;color:var(--muted,#91a0b5)}
.wallet-page .content-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;align-items:stretch;margin:0 0 20px}
.wallet-page .panel{min-width:0;border:1px solid var(--line,#26364a);border-radius:16px;background:var(--panel,#101b2b);box-shadow:0 10px 28px rgba(0,0,0,.10)}
.wallet-page .content-grid>.panel{height:100%;min-height:100%}
.wallet-page .panel>.panel-head{min-height:78px;padding:17px 20px;gap:12px}
.wallet-page .panel-head h2{font-size:16px;font-weight:800;letter-spacing:-.015em}
.wallet-page .panel-head .eyebrow{font-size:9px;font-weight:800;letter-spacing:.12em}
.wallet-page .panel>.muted{margin:18px 20px 0;font-size:12px;line-height:1.7}
.wallet-page .panel>.form-stack{margin:18px 20px 22px;gap:15px}
.wallet-page .form-stack label{display:flex;flex-direction:column;gap:7px;color:#b9c5d3;font-size:11px;font-weight:750;line-height:1.4}
.wallet-page .form-stack input,.wallet-page .form-stack select,.wallet-page .form-stack textarea{width:100%;min-height:45px;padding:11px 13px;border:1px solid #34445a;border-radius:10px;background:#0a1422;color:#eef3f8;font-size:13px;transition:border-color .18s,box-shadow .18s}
.wallet-page .form-stack input::placeholder,.wallet-page .form-stack textarea::placeholder{color:#718198}
.wallet-page .form-stack input:focus,.wallet-page .form-stack select:focus,.wallet-page .form-stack textarea:focus{border-color:#69b8e6;outline:0;box-shadow:0 0 0 3px rgba(105,184,230,.13)}
.wallet-page .form-stack textarea{min-height:88px;resize:vertical}
.wallet-page .form-stack .button{width:100%;min-height:46px;border-radius:10px;font-weight:800}
.wallet-page .panel>.button{margin:18px 20px 22px}
.wallet-page .panel>.page-action-row{margin:16px 20px 20px}
.wallet-page #deposit-address-box{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px;border:1px solid rgba(105,184,230,.25);background:rgba(39,103,145,.12)}
.wallet-page #deposit-address-box>div{min-width:0}
.wallet-page #deposit-address{margin:7px 0;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:12px;line-height:1.6;overflow-wrap:anywhere}
.wallet-page #deposit-address-box button{flex:0 0 auto;min-height:38px}
.wallet-page .panel>.table-wrap{border-top:1px solid var(--line,#26364a)}
.wallet-page .data-table{width:100%;min-width:880px;border-collapse:separate;border-spacing:0}
.wallet-page .data-table th{padding:13px 15px;background:rgba(255,255,255,.025);color:#8fa0b5;font-size:9px;font-weight:850;letter-spacing:.1em;white-space:nowrap}
.wallet-page .data-table td{padding:14px 15px;border-top:1px solid rgba(120,145,170,.10);font-size:11px;line-height:1.5;vertical-align:middle}
.wallet-page .data-table tbody tr:hover{background:rgba(105,184,230,.035)}
.wallet-page .table-status{display:inline-flex;align-items:center;justify-content:center;padding:5px 9px;border-radius:999px;font-size:9px;font-weight:850;letter-spacing:.06em}
.wallet-page .pagination-wrap{padding:14px 18px}
.wallet-page .wallet-empty{padding:34px 20px!important}
.wallet-page .wallet-empty svg{color:#78bfe5}
.wallet-page .wallet-topup-form{max-width:560px}
.wallet-page .wallet-topup-form .muted{margin:0;font-size:11px}
.wallet-page .content-grid .panel>.muted+.form-stack{margin-top:18px}
.wallet-page .content-grid .panel>.muted+.button{margin:18px 20px 22px}
@media(max-width:900px){
 .wallet-page .content-grid{grid-template-columns:1fr;gap:15px}
 .wallet-page .content-grid>.panel{height:auto}
}
@media(max-width:640px){
 .wallet-page .wallet-account-switch{gap:10px}
 .wallet-page .wallet-account-option{min-height:126px;padding:16px}
 .wallet-page .wallet-account-option>strong{font-size:25px}
 .wallet-page .metric-grid,.wallet-page .wallet-detail-metrics{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
 .wallet-page .metric-card{min-height:112px;padding:15px}
 .wallet-page .metric-card>strong{font-size:23px}
 .wallet-page .panel>.panel-head{min-height:70px;padding:14px 15px}
 .wallet-page .panel>.muted{margin:15px 15px 0}
 .wallet-page .panel>.form-stack{margin:15px 15px 18px}
 .wallet-page .panel>.button,.wallet-page .content-grid .panel>.muted+.button{margin:15px}
 .wallet-page .panel>.page-action-row{margin:14px 15px 18px}
 .wallet-page .panel>.page-action-row .button{flex:1}
}
@media(max-width:420px){
 .wallet-page .wallet-account-switch{grid-template-columns:1fr}
 .wallet-page .wallet-account-option{min-height:112px}
 .wallet-page .metric-grid,.wallet-page .wallet-detail-metrics{grid-template-columns:1fr}
 .wallet-page .metric-card{min-height:100px}
 .wallet-page .page-action-row{flex-direction:column;align-items:stretch}
 .wallet-page .page-action-row .button{width:100%}
 .wallet-page #deposit-address-box{align-items:stretch;flex-direction:column}
 .wallet-page #deposit-address-box button{width:100%}
}

</style>
<div class="wallet-page">

<div class="wallet-account-switch">
    <a href="{{ route('wallet-requests.index', ['account' => 'real']) }}" class="wallet-account-option {{ $selectedAccount === 'real' ? 'active' : '' }}" aria-current="{{ $selectedAccount === 'real' ? 'page' : 'false' }}">
        <span class="wallet-account-name"><span class="wallet-section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg></span>Real account</span>
        <strong>@money($realBalance) <small>{{ $currency }}</small></strong>
        <span class="wallet-account-hint">Deposits and withdrawals</span>
    </a>
    <a href="{{ route('wallet-requests.index', ['account' => 'demo']) }}" class="wallet-account-option {{ $selectedAccount === 'demo' ? 'active' : '' }}" aria-current="{{ $selectedAccount === 'demo' ? 'page' : 'false' }}">
        <span class="wallet-account-name"><span class="wallet-section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-chart"/></svg></span>Demo account</span>
        <strong>@money($demoBalance) <small>{{ $currency }}</small></strong>
        <span class="wallet-account-hint">Simulation funds and demo trading</span>
    </a>
</div>
<div class="page-action-row">
    @if($selectedAccount === 'real')
        <a href="#deposit-form" class="button button-primary" id="wallet-deposit-shortcut"><svg class="nav-icon"><use href="#i-plus"/></svg> Deposit funds</a>
    @else
        <a href="#demo-topup-form" class="button button-primary"><svg class="nav-icon"><use href="#i-plus"/></svg> Add demo funds</a>
    @endif
    <a href="{{ route('statement.index', ['wallet' => $selectedAccount === 'demo' ? 'PAPER_WALLET' : 'REAL_WALLET']) }}" class="button button-secondary"><svg class="nav-icon"><use href="#i-receipt"/></svg> {{ ucfirst($selectedAccount) }} statement</a>
    @if($selectedAccount === 'real')
        <a href="{{ route('kyc.index') }}" class="button button-secondary"><svg class="nav-icon"><use href="#i-shield"/></svg> Open identity verification</a>
    @endif
</div>
@if(session('status'))<div class="notice-banner"><div><strong>{{ session('status') }}</strong></div></div>@endif
@if($errors->any())<div class="notice-banner"><div><strong>Check the submitted details</strong><p>{{ $errors->first() }}</p></div></div>@endif

@if($selectedAccount === 'demo')
    <div class="metric-grid wallet-detail-metrics">
        <article class="metric-card"><span class="metric-label">CURRENT DEMO BALANCE</span><strong>@money($selectedBalance)</strong><small>{{ $currency }} simulation credits</small></article>
        <article class="metric-card"><span class="metric-label">ACCOUNT TYPE</span><strong>Demo</strong><small>Independent from your real wallet</small></article>
    </div>
    <section class="panel" id="demo-topup-form">
        <div class="panel-head"><div><span class="wallet-section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg></span><span class="eyebrow">DEMO ACCOUNT</span><h2>Manage demo funds</h2></div><span class="table-status verified">ACTIVE</span></div>
        <p class="muted">Use this balance to practise fixed-time trading and simulated conversions. Demo funds have no cash value and cannot be withdrawn or transferred to your real account.</p>
        <form id="wallet-demo-topup-form" class="form-stack wallet-topup-form">
            @csrf
            <label>Add demo funds (USD)<input type="number" name="amount" min="100" max="20000" step="0.01" value="1000" required></label>
            <button class="button button-primary" type="submit">Add demo funds</button>
            <p class="muted" id="wallet-demo-topup-message" role="status"></p>
        </form>
        <div class="page-action-row"><a class="button button-secondary" href="{{ route('trading.index') }}">Open demo trading</a><a class="button button-secondary" href="{{ route('convert.index') }}">Convert demo assets</a></div>
    </section>
@else
    <x-client-attention title="Real wallet" tag="REAL ACCOUNT"><p>Deposit and withdrawal requests are recorded here. Deposits are credited after transaction verification. Withdrawals are processed after identity approval and operations review.</p></x-client-attention>
    <div class="content-grid">
        <section class="panel" id="deposit-form">
            <div class="panel-head"><div><span class="wallet-section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-arrow"/></svg></span><span class="eyebrow">ADD REAL FUNDS</span><h2>Deposit crypto</h2></div></div>
            <p class="muted">Choose the correct asset and network, send funds to the displayed address, then submit the transaction hash. Your real wallet is credited after operations verifies the transaction.</p>
            @if($methods->where('deposits_enabled', true)->isEmpty())
                <p class="muted">Available deposit networks will appear here. Always use the asset, network and wallet address displayed in your account before sending funds.</p>
            @else
                <form method="POST" action="{{ route('wallet-requests.deposit') }}" class="form-stack">@csrf
                    <label>Asset / network<select name="method_id" id="deposit-method" required><option value="">Choose network</option>@foreach($methods->where('deposits_enabled', true) as $method)<option value="{{ $method->id }}" data-address="{{ $method->wallet_address }}" data-memo-label="{{ $method->memo_label }}" data-memo="{{ $method->memo_value }}" data-instructions="{{ $method->instructions }}">{{ $method->asset }} · {{ $method->network }}</option>@endforeach</select></label>
                    <div class="notice-banner" id="deposit-address-box" hidden><div><strong>Deposit address</strong><p id="deposit-address" style="overflow-wrap:anywhere"></p><p id="deposit-memo"></p><p id="deposit-instructions"></p></div><button class="button button-secondary" type="button" id="copy-deposit-address">Copy</button></div>
                    <label>Crypto amount sent<input name="asset_amount" type="number" min="0.00000001" step="0.000000000001" required></label>
                    <label>Estimated USD value<input name="amount_usd" type="number" min="1" step="0.00000001" required></label>
                    <label>Transaction hash / TxID<input name="transaction_hash" maxlength="255" required></label>
                    <label>Note (optional)<textarea name="client_note" maxlength="1000"></textarea></label>
                    <button class="button button-primary" type="submit">Submit deposit for confirmation</button>
                </form>
            @endif
        </section>
        <section class="panel">
            <div class="panel-head"><div><span class="wallet-section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-wallet"/></svg></span><span class="eyebrow">PAYOUT</span><h2>Withdraw real funds</h2></div></div>
            @if(!$kycVerified)
                <p class="muted">Withdrawals are locked until your KYC documents are approved. Complete identity verification on the dedicated KYC page. You do not need to deposit funds first.</p>
                <a class="button button-secondary" href="{{ route('kyc.index') }}">Complete identity verification</a>
            @elseif($methods->where('withdrawals_enabled', true)->isEmpty())
                <p class="muted">Withdrawal networks configured for your account will appear here. Contact support if you need help with an available network.</p>
            @else
                <p class="muted">Your identity is verified. Submit a payout request to an eligible wallet address.</p>
                <form method="POST" action="{{ route('wallet-requests.withdraw') }}" class="form-stack">@csrf
                    <label>Asset / network<select name="method_id" required><option value="">Choose network</option>@foreach($methods->where('withdrawals_enabled', true) as $method)<option value="{{ $method->id }}">{{ $method->asset }} · {{ $method->network }}</option>@endforeach</select></label>
                    <label>Amount (USD)<input name="amount_usd" type="number" min="1" step="0.00000001" required></label>
                    <label>Your receiving wallet address<input name="wallet_address" maxlength="255" required></label>
                    <label>Memo / destination tag (if required)<input name="wallet_memo" maxlength="255"></label>
                    <label>Note (optional)<textarea name="client_note" maxlength="1000"></textarea></label>
                    <button class="button button-primary" type="submit">Request withdrawal</button>
                    <p class="muted">Funds are reserved when you submit. If rejected, the reserve is returned. A payout is marked paid after the administrator records the transaction hash.</p>
                </form>
            @endif
        </section>
    </div>
    <section class="panel"><div class="panel-head"><div><span class="wallet-section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-receipt"/></svg></span><span class="eyebrow">REAL WALLET</span><h2>Deposit and withdrawal history</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>REQUEST</th><th>TYPE</th><th>AMOUNT</th><th>ASSET / NETWORK</th><th>STATUS</th><th>CREATED</th><th>ADMIN NOTE</th></tr></thead><tbody>
    @forelse($requests as $item)<tr><td>#{{ $item->id }}</td><td>{{ strtoupper($item->type) }}</td><td>@money($item->amount_usd) USD</td><td>{{ $item->asset }} · {{ $item->network }}</td><td><span class="table-status {{ in_array($item->status,['confirmed','paid']) ? 'verified' : 'pending' }}">{{ strtoupper($item->status) }}</span></td><td>{{ $item->created_at }}</td><td>{{ $item->admin_note ?: '—' }}</td></tr>@empty<tr><td colspan="7" class="table-empty">No requests submitted.</td></tr>@endforelse
    </tbody></table></div><div class="pagination-wrap">{{ $requests->links() }}</div></section>
@endif
</div>
@endsection
@push('scripts')
<script>
const methodSelect=document.getElementById('deposit-method');
methodSelect?.addEventListener('change',()=>{const opt=methodSelect.selectedOptions[0];const box=document.getElementById('deposit-address-box');if(!opt?.value){box.hidden=true;return;}box.hidden=false;document.getElementById('deposit-address').textContent=opt.dataset.address||'';document.getElementById('deposit-memo').textContent=opt.dataset.memo?((opt.dataset.memoLabel||'Memo')+': '+opt.dataset.memo):'';document.getElementById('deposit-instructions').textContent=opt.dataset.instructions||'';});
document.getElementById('copy-deposit-address')?.addEventListener('click',()=>navigator.clipboard.writeText(document.getElementById('deposit-address').textContent));
document.getElementById('wallet-demo-topup-form')?.addEventListener('submit',async(event)=>{event.preventDefault();const form=event.currentTarget;const button=form.querySelector('button[type="submit"]');const message=document.getElementById('wallet-demo-topup-message');button.disabled=true;message.textContent='Processing demo credit…';try{const result=await window.shellAjax(@json(route('portfolio.demo.top-up')),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({amount:form.amount.value,request_key:crypto.randomUUID()})});message.textContent=result.message;window.shellToast(result.message);window.location.href=@json(route('wallet-requests.index',['account'=>'demo']));}catch(error){message.textContent=error.message;window.shellToast(error.message,'error');}finally{button.disabled=false;}});
</script>
@endpush
