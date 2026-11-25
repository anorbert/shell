@extends('layouts.app')
@section('title','Paper conversion')
@section('eyebrow','SIMULATION EXCHANGE')
@section('heading','Currency conversion')

@section('content')
@php
    $formatConversion = fn ($value) => is_numeric($value) ? number_format((float) $value, 8, '.', ',') : '—';
@endphp

<style>
    .conversion-page{display:grid;gap:20px}
    .conversion-hero{position:relative;overflow:hidden;padding:26px;border:1px solid rgba(92,153,226,.24);border-radius:20px;background:linear-gradient(120deg,rgba(29,111,164,.20),rgba(12,27,52,.94) 62%,rgba(201,145,10,.10))}
    .conversion-hero:after{content:"";position:absolute;width:220px;height:220px;right:-65px;top:-115px;border-radius:50%;background:rgba(54,139,206,.12);pointer-events:none}
    .conversion-hero-content{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap}
    .conversion-hero h2{margin:7px 0 6px;font-size:clamp(22px,3vw,30px);letter-spacing:-.03em}
    .conversion-hero p{max-width:650px;margin:0;color:var(--muted,#9eacc1);line-height:1.6}
    .conversion-hero-icon,.conversion-icon{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;border:1px solid rgba(120,174,230,.22);background:rgba(36,105,163,.16);color:#8bc5ff}
    .conversion-hero-icon{width:58px;height:58px;border-radius:17px}
    .conversion-hero-icon svg{width:29px;height:29px}
    .conversion-layout{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(280px,.75fr);gap:18px;align-items:start}
    .conversion-panel{min-width:0;padding:22px;border:1px solid var(--border,rgba(130,153,184,.18));border-radius:18px;background:var(--panel,rgba(13,25,46,.88))}
    .conversion-panel-head{display:flex;align-items:center;gap:12px;margin-bottom:20px}
    .conversion-icon{width:42px;height:42px;border-radius:13px}
    .conversion-icon svg{width:20px;height:20px}
    .conversion-panel-head h3{margin:0;font-size:17px}
    .conversion-panel-head p{margin:4px 0 0;color:var(--muted,#9eacc1);font-size:12px}
    .conversion-fields{display:grid;gap:14px}
    .conversion-field{display:grid;gap:7px}
    .conversion-field label{font-size:12px;font-weight:700;color:var(--muted,#aab7ca)}
    .conversion-field select,.conversion-field input{width:100%;min-height:48px;padding:11px 13px;border:1px solid var(--border,rgba(130,153,184,.24));border-radius:11px;background:var(--surface,rgba(7,17,34,.8));color:var(--text,#edf3fb);outline:none}
    .conversion-field select:focus,.conversion-field input:focus{border-color:#398bd0;box-shadow:0 0 0 3px rgba(57,139,208,.13)}
    .conversion-field input{font-variant-numeric:tabular-nums}
    .conversion-balance{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;color:var(--muted,#9eacc1);font-size:12px}
    .conversion-balance strong{color:var(--text,#edf3fb);font-variant-numeric:tabular-nums}
    .conversion-swap{display:flex;align-items:center;justify-content:center;margin:-2px 0}
    .conversion-swap button{width:38px;height:38px;border:1px solid rgba(82,153,218,.3);border-radius:50%;background:rgba(29,111,164,.14);color:#91caff;cursor:pointer;display:grid;place-items:center}
    .conversion-swap svg{width:17px;height:17px}
    .conversion-primary{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;min-height:48px;padding:12px 16px;border:0;border-radius:11px;background:linear-gradient(100deg,#1d6fa4,#2789c5);color:#fff;font-weight:800;cursor:pointer;transition:filter .18s,transform .18s}
    .conversion-primary:hover:not(:disabled){filter:brightness(1.1);transform:translateY(-1px)}
    .conversion-primary:disabled{opacity:.55;cursor:not-allowed}
    .conversion-primary svg{width:17px;height:17px}
    .conversion-preview{margin-top:16px;padding:17px;border:1px solid rgba(47,170,125,.28);border-radius:14px;background:rgba(22,122,88,.09)}
    .conversion-preview[hidden]{display:none}
    .conversion-preview-label{display:flex;align-items:center;gap:7px;color:#76d7ae;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    .conversion-preview-output{margin:9px 0 5px;font-size:clamp(23px,3vw,30px);font-weight:850;letter-spacing:-.03em;font-variant-numeric:tabular-nums;overflow-wrap:anywhere}
    .conversion-preview-details{margin:0 0 15px;color:var(--muted,#aab7ca);font-size:12px;line-height:1.6}
    .conversion-note{display:flex;gap:11px;padding:13px;border:1px solid var(--border,rgba(130,153,184,.18));border-radius:12px;background:rgba(255,255,255,.025)}
    .conversion-note+.conversion-note{margin-top:10px}
    .conversion-note svg{width:18px;height:18px;flex:0 0 auto;color:#83baf0;margin-top:2px}
    .conversion-note strong{display:block;font-size:13px;margin-bottom:3px}
    .conversion-note span{display:block;color:var(--muted,#9eacc1);font-size:12px;line-height:1.55}
    .conversion-table-wrap{overflow-x:auto}
    .conversion-table{width:100%;border-collapse:collapse;min-width:650px}
    .conversion-table th{padding:12px 14px;text-align:left;color:var(--muted,#9eacc1);font-size:10px;letter-spacing:.08em}
    .conversion-table td{padding:14px;border-top:1px solid var(--border,rgba(130,153,184,.14));font-size:12px;white-space:nowrap}
    .conversion-number{font-variant-numeric:tabular-nums;font-weight:700}
    .conversion-status{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:20px;background:rgba(44,171,124,.12);color:#72d5a9;font-size:10px;font-weight:800}
    .conversion-empty{text-align:center;padding:28px;color:var(--muted,#9eacc1)}
    @media(max-width:850px){.conversion-layout{grid-template-columns:1fr}}
    @media(max-width:560px){.conversion-hero,.conversion-panel{padding:17px}.conversion-hero-icon{width:48px;height:48px}.conversion-hero-icon svg{width:24px;height:24px}}
</style>

<div class="conversion-page">
    <x-client-attention title="Convert assets in your demo wallet" tag="PAPER ONLY">
        <p>Exchange supported demo assets using current market quotes. USD and USDT are treated as a 1:1 reference for simulation purposes. No real cryptocurrency is purchased, held or transferred.</p>
    </x-client-attention>

    <section class="conversion-hero">
        <div class="conversion-hero-content">
            <div>
                <span class="eyebrow">DEMO WALLET · SIMULATED EXCHANGE</span>
                <h2>Convert your demo assets</h2>
                <p>Preview a market-based conversion, review the exact amounts and confirm it to update your demo-wallet ledger.</p>
            </div>
            <div class="conversion-hero-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9a4 4 0 0 0-4 4v1"/><path d="m16 3 4 4-4 4"/><path d="M4 17h9a4 4 0 0 0 4-4v-1"/><path d="m8 21-4-4 4-4"/></svg>
            </div>
        </div>
    </section>

    <div class="conversion-layout">
        <section class="conversion-panel">
            <div class="conversion-panel-head">
                <span class="conversion-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h14l-4-4"/><path d="M17 17H3l4 4"/><path d="M21 7v5"/><path d="M3 17v-5"/></svg>
                </span>
                <div><h3>New paper conversion</h3><p>Choose the assets and amount to exchange</p></div>
            </div>

            <form id="conversion-form" class="conversion-fields">
                <input type="hidden" name="request_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                <input type="hidden" name="source_quote_at">
                <input type="hidden" name="target_quote_at">

                <div class="conversion-field">
                    <label for="conversion-from">FROM ASSET</label>
                    <select name="from_currency" id="conversion-from">
                        @foreach($currencies as $c)
                            <option value="{{ $c }}" data-balance="{{ $balances[$c] ?? '0.00000000' }}" @selected($c === $currency)>{{ $c }}</option>
                        @endforeach
                    </select>
                    <div class="conversion-balance"><span>Available demo balance</span><strong id="conversion-available">{{ $formatConversion($balances[$currency] ?? '0') }} {{ $currency }}</strong></div>
                </div>

                <div class="conversion-swap">
                    <button type="button" id="swap-conversion" aria-label="Swap source and target assets" title="Swap assets">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 7 3-3 3 3"/><path d="M10 4v12"/><path d="m17 17-3 3-3-3"/><path d="M14 20V8"/></svg>
                    </button>
                </div>

                <div class="conversion-field">
                    <label for="conversion-to">TO ASSET</label>
                    <select name="to_currency" id="conversion-to">
                        @foreach($currencies as $c)
                            <option value="{{ $c }}" @selected($c === 'BTC')>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="conversion-field">
                    <label for="conversion-amount">AMOUNT TO CONVERT</label>
                    <input id="conversion-amount" name="amount" type="number" min="0.00000001" step="0.00000001" max="{{ $balances[$currency] ?? '0' }}" value="10.00000000" inputmode="decimal" autocomplete="off" required>
                    <div class="conversion-balance"><span>Maximum per conversion</span><span>8 decimal places · demo only</span></div>
                </div>

                <button class="conversion-primary" id="preview-conversion" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                    Preview conversion
                </button>

                <div id="conversion-preview" class="conversion-preview" hidden aria-live="polite">
                    <div class="conversion-preview-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="m5 12 4 4L19 6"/></svg>
                        Indicative output
                    </div>
                    <h2 id="conversion-output" class="conversion-preview-output">—</h2>
                    <p id="conversion-details" class="conversion-preview-details"></p>
                    <button class="conversion-primary" id="confirm-conversion" type="button">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7 10 17l-5-5"/><path d="M20 12a8 8 0 1 1-4.7-7.3"/></svg>
                        Confirm paper conversion
                    </button>
                </div>
            </form>
        </section>

        <aside class="conversion-panel">
            <div class="conversion-panel-head">
                <span class="conversion-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 7h.01"/></svg>
                </span>
                <div><h3>Conversion details</h3><p>Understand what happens when you confirm</p></div>
            </div>
            <div class="conversion-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <div><strong>Live market quote</strong><span>Crypto conversion previews use the latest available market price. USD and USDT use a 1:1 simulation reference.</span></div>
            </div>
            <div class="conversion-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path d="m9 12 2 2 4-4"/></svg>
                <div><strong>Quote revalidation</strong><span>The market quote is checked again at confirmation. If it changes, refresh the preview before proceeding.</span></div>
            </div>
            <div class="conversion-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 9h10M7 13h6"/></svg>
                <div><strong>Ledger-recorded</strong><span>A successful conversion records the source debit and target credit in your demo-wallet ledger.</span></div>
            </div>
            <div class="conversion-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 8l8 8M16 8l-8 8"/></svg>
                <div><strong>No real-world settlement</strong><span>This is a paper simulation. No real funds, exchange account or blockchain transfer is involved.</span></div>
            </div>
        </aside>
    </div>

    <section class="conversion-panel">
        <div class="conversion-panel-head">
            <span class="conversion-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-4 4"/></svg>
            </span>
            <div><h3>Recent conversions</h3><p>Your latest demo-asset exchanges</p></div>
        </div>
        <div class="conversion-table-wrap">
            <table class="conversion-table">
                <thead><tr><th>DATE</th><th>FROM</th><th>TO</th><th>MARKET RATE (USDT)</th><th>STATUS</th></tr></thead>
                <tbody>
                    @forelse($conversions as $c)
                        <tr>
                            <td>{{ $c->created_at }}</td>
                            <td class="conversion-number">{{ $formatConversion($c->amount_in) }} {{ $c->from_currency }}</td>
                            <td class="conversion-number">{{ $formatConversion($c->amount_out) }} {{ $c->to_currency }}</td>
                            <td class="conversion-number">{{ $formatConversion($c->source_price) }} / {{ $formatConversion($c->target_price) }}</td>
                            <td><span class="conversion-status"><span aria-hidden="true">●</span> PAPER · COMPLETED</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="conversion-empty">No paper conversions recorded yet. Your completed conversions will appear here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('conversion-form');
    if (!form) return;

    const fromSelect = form.elements.from_currency;
    const toSelect = form.elements.to_currency;
    const amountInput = form.elements.amount;
    const availableLabel = document.getElementById('conversion-available');
    const preview = document.getElementById('conversion-preview');
    const previewButton = document.getElementById('preview-conversion');
    const confirmButton = document.getElementById('confirm-conversion');
    const swapButton = document.getElementById('swap-conversion');
    let conversionReady = false;
    let previewSignature = '';

    // Keep every displayed asset quantity and market rate at exactly 8 decimal places.
    const fixedDecimal = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 8,
        maximumFractionDigits: 8,
        useGrouping: true
    });
    const format = value => fixedDecimal.format(Number(value));
    const signature = () => [fromSelect.value, toSelect.value, amountInput.value].join('|');
    const payload = () => Object.fromEntries(new FormData(form).entries());

    function invalidatePreview() {
        conversionReady = false;
        previewSignature = '';
        preview.hidden = true;
        confirmButton.disabled = true;
    }

    function updateAvailableBalance() {
        const option = fromSelect.options[fromSelect.selectedIndex];
        const balance = option?.dataset.balance || '0.00000000';
        availableLabel.textContent = format(balance) + ' ' + fromSelect.value;
        amountInput.max = balance;
        invalidatePreview();
    }

    function validateSelection() {
        if (fromSelect.value === toSelect.value) {
            window.shellToast('Choose two different assets to convert.', 'error');
            return false;
        }
        const amount = amountInput.value.trim();
        if (!/^(0|[1-9][0-9]{0,9})(\.[0-9]{1,8})?$/.test(amount) || Number(amount) <= 0) {
            window.shellToast('Enter a valid amount with up to 8 decimal places.', 'error');
            return false;
        }
        const available = fromSelect.options[fromSelect.selectedIndex]?.dataset.balance || '0';
        if (Number(amount) > Number(available)) {
            window.shellToast('The amount exceeds your available demo balance.', 'error');
            return false;
        }
        return true;
    }

    fromSelect.addEventListener('change', updateAvailableBalance);
    toSelect.addEventListener('change', invalidatePreview);
    amountInput.addEventListener('input', invalidatePreview);

    swapButton.addEventListener('click', () => {
        const previousFrom = fromSelect.value;
        fromSelect.value = toSelect.value;
        toSelect.value = previousFrom;
        updateAvailableBalance();
    });

    previewButton.addEventListener('click', async () => {
        if (!validateSelection()) return;
        previewButton.disabled = true;
        previewButton.textContent = 'Loading live quote…';
        invalidatePreview();
        try {
            const quote = await window.shellAjax(@json(route('convert.quote')), {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload())
            });

            // Do not display a response if the user changed the form while the quote was loading.
            if (signature() !== [quote.from_currency, quote.to_currency, quote.amount_in].join('|')) {
                window.shellToast('Your selection changed. Preview the conversion again.', 'error');
                return;
            }

            form.elements.source_quote_at.value = quote.source_quote_at || '';
            form.elements.target_quote_at.value = quote.target_quote_at || '';
            document.getElementById('conversion-output').textContent = format(quote.amount_out) + ' ' + quote.to_currency;
            document.getElementById('conversion-details').textContent =
                'Rate basis: ' + format(quote.source_price) + ' USDT per ' + quote.from_currency +
                ' ÷ ' + format(quote.target_price) + ' USDT per ' + quote.to_currency +
                '. Output is rounded down to 8 decimal places. Simulation only.';
            previewSignature = signature();
            conversionReady = true;
            preview.hidden = false;
            confirmButton.disabled = false;
        } catch (error) {
            window.shellToast(error.message || 'Unable to retrieve a conversion quote.', 'error');
            invalidatePreview();
        } finally {
            previewButton.disabled = false;
            previewButton.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg> Preview conversion';
        }
    });

    confirmButton.addEventListener('click', async () => {
        if (!conversionReady || previewSignature !== signature()) {
            invalidatePreview();
            window.shellToast('Preview the current conversion before confirming.', 'error');
            return;
        }
        confirmButton.disabled = true;
        confirmButton.textContent = 'Confirming conversion…';
        try {
            const result = await window.shellAjax(@json(route('convert.paper')), {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload())
            });
            conversionReady = false;
            window.shellToast(result.message || 'Paper conversion completed.');
            window.location.reload();
        } catch (error) {
            window.shellToast(error.message || 'Conversion could not be completed.', 'error');
            invalidatePreview();
        } finally {
            confirmButton.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7 10 17l-5-5"/><path d="M20 12a8 8 0 1 1-4.7-7.3"/></svg> Confirm paper conversion';
            if (conversionReady) confirmButton.disabled = false;
        }
    });

    updateAvailableBalance();
})();
</script>
@endpush
