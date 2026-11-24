@extends('layouts.app')
@section('title','Wallet')
@section('eyebrow','WALLET CENTER')
@section('heading','My wallet')
@section('content')
<div class="wallet-account-switch">
    <a href="{{ route('wallet-requests.index', ['account' => 'real']) }}" class="wallet-account-option {{ $selectedAccount === 'real' ? 'active' : '' }}" aria-current="{{ $selectedAccount === 'real' ? 'page' : 'false' }}">
        <span class="wallet-account-name">Real account</span>
        <strong>@money($realBalance) <small>{{ $currency }}</small></strong>
        <span class="wallet-account-hint">Deposits and withdrawals</span>
    </a>
    <a href="{{ route('wallet-requests.index', ['account' => 'demo']) }}" class="wallet-account-option {{ $selectedAccount === 'demo' ? 'active' : '' }}" aria-current="{{ $selectedAccount === 'demo' ? 'page' : 'false' }}">
        <span class="wallet-account-name">Demo account</span>
        <strong>@money($demoBalance) <small>{{ $currency }}</small></strong>
        <span class="wallet-account-hint">Simulation funds and demo trading</span>
    </a>
</div>
<div class="page-action-row">
    <a href="{{ route('statement.index', ['wallet' => $selectedAccount === 'demo' ? 'PAPER_WALLET' : 'REAL_WALLET']) }}" class="button button-secondary"><svg class="nav-icon"><use href="#i-receipt"/></svg> {{ ucfirst($selectedAccount) }} statement</a>
</div>
@if(session('status'))<div class="notice-banner"><div><strong>{{ session('status') }}</strong></div></div>@endif
@if($errors->any())<div class="notice-banner"><div><strong>Check the submitted details</strong><p>{{ $errors->first() }}</p></div></div>@endif

@if($selectedAccount === 'demo')
    <div class="metric-grid wallet-detail-metrics">
        <article class="metric-card"><span class="metric-label">CURRENT DEMO BALANCE</span><strong>@money($selectedBalance)</strong><small>{{ $currency }} simulation credits</small></article>
        <article class="metric-card"><span class="metric-label">ACCOUNT TYPE</span><strong>Demo</strong><small>Independent from your real wallet</small></article>
    </div>
    <section class="panel">
        <div class="panel-head"><div><span class="eyebrow">DEMO ACCOUNT</span><h2>Manage demo funds</h2></div><span class="table-status verified">ACTIVE</span></div>
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
    <div class="metric-grid wallet-detail-metrics">
        <article class="metric-card"><span class="metric-label">AVAILABLE REAL BALANCE</span><strong>@money($selectedBalance)</strong><small>{{ $currency }} · real wallet</small></article>
        <article class="metric-card"><span class="metric-label">ACCOUNT TYPE</span><strong>Real</strong><small>Independent from demo trading</small></article>
    </div>
    <div class="content-grid">
        <section class="panel" id="deposit-form">
            <div class="panel-head"><div><span class="eyebrow">ADD REAL FUNDS</span><h2>Deposit crypto</h2></div></div>
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
            <div class="panel-head"><div><span class="eyebrow">PAYOUT</span><h2>Withdraw real funds</h2></div></div>
            @if(!$hasSuccessfulDeposit)
                <p class="muted">Withdrawals and identity verification become available after your first real-money deposit has been confirmed by our operations team. Demo funds do not qualify.</p>
                <a class="button button-secondary" href="#deposit-form">Make your first real deposit</a>
                @if($requests->where('type', 'deposit')->where('status', 'pending')->isNotEmpty())
                    <p class="muted">Your deposit is awaiting verification. KYC and withdrawals will unlock after it is confirmed.</p>
                @endif
            @elseif(!$kycVerified)
                <p class="muted">Your first real-money deposit is confirmed. Submit your identity documents below. Withdrawals unlock only after an administrator approves your KYC.</p>
                @if($kyc?->review_note)<p class="muted">Admin review note: {{ $kyc->review_note }}</p>@endif
                <form method="POST" action="{{ route('wallet-requests.kyc') }}" enctype="multipart/form-data" class="form-stack">@csrf
                    <label>Document type<select name="document_type" required><option value="national_id">National ID</option><option value="passport">Passport</option><option value="driving_licence">Driving licence</option></select></label>
                    <label>Document number<input name="document_number" maxlength="120" required></label>
                    <label>Identity document (JPG, PNG or PDF; max 10 MB)<input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required></label>
                    <label>Selfie (JPG or PNG; max 5 MB)<input type="file" name="selfie" accept=".jpg,.jpeg,.png" required></label>
                    <button class="button button-primary" type="submit" {{ ($kyc?->status ?? '') === 'pending' ? 'disabled' : '' }}>{{ ($kyc?->status ?? '') === 'pending' ? 'KYC under review' : 'Submit identity verification' }}</button>
                </form>
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
    <section class="panel"><div class="panel-head"><div><span class="eyebrow">REAL WALLET</span><h2>Deposit and withdrawal history</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>REQUEST</th><th>TYPE</th><th>AMOUNT</th><th>ASSET / NETWORK</th><th>STATUS</th><th>CREATED</th><th>ADMIN NOTE</th></tr></thead><tbody>
    @forelse($requests as $item)<tr><td>#{{ $item->id }}</td><td>{{ strtoupper($item->type) }}</td><td>@money($item->amount_usd) USD</td><td>{{ $item->asset }} · {{ $item->network }}</td><td><span class="table-status {{ in_array($item->status,['confirmed','paid']) ? 'verified' : 'pending' }}">{{ strtoupper($item->status) }}</span></td><td>{{ $item->created_at }}</td><td>{{ $item->admin_note ?: '—' }}</td></tr>@empty<tr><td colspan="7" class="table-empty">No requests submitted.</td></tr>@endforelse
    </tbody></table></div><div class="pagination-wrap">{{ $requests->links() }}</div></section>
@endif
@endsection
@push('scripts')
<script>
const methodSelect=document.getElementById('deposit-method');
methodSelect?.addEventListener('change',()=>{const opt=methodSelect.selectedOptions[0];const box=document.getElementById('deposit-address-box');if(!opt?.value){box.hidden=true;return;}box.hidden=false;document.getElementById('deposit-address').textContent=opt.dataset.address||'';document.getElementById('deposit-memo').textContent=opt.dataset.memo?((opt.dataset.memoLabel||'Memo')+': '+opt.dataset.memo):'';document.getElementById('deposit-instructions').textContent=opt.dataset.instructions||'';});
document.getElementById('copy-deposit-address')?.addEventListener('click',()=>navigator.clipboard.writeText(document.getElementById('deposit-address').textContent));
document.getElementById('wallet-demo-topup-form')?.addEventListener('submit',async(event)=>{event.preventDefault();const form=event.currentTarget;const button=form.querySelector('button[type="submit"]');const message=document.getElementById('wallet-demo-topup-message');button.disabled=true;message.textContent='Processing demo credit…';try{const result=await window.shellAjax(@json(route('portfolio.demo.top-up')),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({amount:form.amount.value,request_key:crypto.randomUUID()})});message.textContent=result.message;window.shellToast(result.message);window.location.href=@json(route('wallet-requests.index',['account'=>'demo']));}catch(error){message.textContent=error.message;window.shellToast(error.message,'error');}finally{button.disabled=false;}});
</script>
@endpush
