@extends('layouts.app')
@section('title','Portfolio')
@section('eyebrow','WALLET CENTER')
@section('heading','My wallets')
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Two independent wallets</strong><p>Your demo wallet starts with $20,000 in non-withdrawable simulation credits. You can add demo credits. Real wallet funds can only arrive through confirmed real-money deposits; real-money services are not yet connected.</p></div><span class="mode-tag">SEPARATE LEDGERS</span></div>
<div class="metric-grid">
    <article class="metric-card"><span class="metric-label">DEMO WALLET · USD</span><strong>{{ $demoBalance }}</strong><small>Simulation credits only</small></article>
    <article class="metric-card"><span class="metric-label">REAL WALLET · USD</span><strong>{{ $realBalance }}</strong><small>Real deposit balance · starts at zero</small></article>
    <article class="metric-card"><span class="metric-label">DEMO WITHDRAWALS</span><strong>Not allowed</strong><small>Simulation credits have no cash value</small></article>
    <article class="metric-card"><span class="metric-label">REAL DEPOSITS</span><strong>Coming after integration</strong><small>Only confirmed provider settlements count</small></article>
</div>
<div class="content-grid">
    <section class="panel">
        <div class="panel-head"><div><span class="eyebrow">SIMULATION ENVIRONMENT</span><h2>Demo wallet</h2></div><span class="table-status verified">ACTIVE</span></div>
        <p class="muted">Used for demo trading and simulated conversions. Add $100–$20,000 per top-up. Demo credits cannot be withdrawn or transferred to the real wallet. Crypto conversion prices use USDT market quotes as a 1:1 USD reference.</p>
        <div class="metric-card"><span class="metric-label">AVAILABLE USD</span><strong id="demo-wallet-balance">{{ $demoBalance }}</strong></div>
        <button class="button button-primary" type="button" data-modal-open="#demo-topup-modal">Add demo funds</button>
        <div class="table-wrap"><table class="data-table"><thead><tr><th>ASSET</th><th>DEMO BALANCE</th><th>TYPE</th></tr></thead><tbody>
        @forelse($demoBalances as $asset)<tr><td><strong>{{ $asset['currency'] }}</strong></td><td>{{ $asset['balance'] }}</td><td><span class="table-status verified">DEMO</span></td></tr>@empty<tr><td colspan="3" class="table-empty">No demo balances yet.</td></tr>@endforelse
        </tbody></table></div>
        <a class="text-link" href="{{ route('trading.index') }}">Open demo trading ↗</a>
        <span class="muted"> · </span>
        <a class="text-link" href="{{ route('convert.index') }}">Convert demo assets ↗</a>
    </section>
    <section class="panel">
        <div class="panel-head"><div><span class="eyebrow">REAL-MONEY ENVIRONMENT</span><h2>Real wallet</h2></div><span class="table-status pending">NOT FUNDED</span></div>
        <p class="muted">This wallet is independent of demo trading. Its opening balance is $0. It can only be credited after a real deposit is confirmed by an integrated payment provider.</p>
        <div class="metric-card"><span class="metric-label">AVAILABLE USD</span><strong>{{ $realBalance }}</strong></div>
        <div class="notice-banner"><span class="notice-icon">!</span><div><strong>Real deposits are not live yet</strong><p>Do not send funds to Shell until an official deposit method is displayed in the app. No demo top-up or manual balance adjustment should credit this wallet.</p></div></div>
        <p class="muted">Real withdrawals and real-money trading remain disabled pending provider, custody, reconciliation and compliance controls.</p>
    </section>
</div>
<div class="modal" id="demo-topup-modal" aria-hidden="true">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="demo-topup-title">
        <button class="modal-close" type="button" data-modal-close aria-label="Close">×</button>
        <span class="eyebrow">SIMULATION ONLY</span>
        <h2 id="demo-topup-title">Add demo funds</h2>
        <p class="muted">Choose an amount between $100 and $20,000. This creates simulation credits only, not real money.</p>
        <form id="demo-topup-form" class="form-stack">
            @csrf
            <label>Amount (USD)<input type="number" name="amount" min="100" max="20000" step="0.01" value="1000" required></label>
            <button class="button button-primary button-full" type="submit">Add simulation funds</button>
        </form>
        <p class="muted" id="demo-topup-message" role="status"></p>
    </div>
</div>
<script>
document.getElementById('demo-topup-form')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const button = form.querySelector('button[type="submit"]');
    const message = document.getElementById('demo-topup-message');
    button.disabled = true;
    message.textContent = 'Processing demo credit…';
    try {
        const result = await window.shellAjax(@json(route('portfolio.demo.top-up')), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ amount: form.amount.value, request_key: crypto.randomUUID() }),
        });
        document.getElementById('demo-wallet-balance').textContent = result.balance;
        message.textContent = result.message;
        window.shellToast(result.message);
        form.reset();
    } catch (error) {
        message.textContent = error.message;
        window.shellToast(error.message, 'error');
    } finally {
        button.disabled = false;
    }
});
</script>
@endsection
