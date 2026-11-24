@extends('layouts.app')
@section('title','Paper conversion') @section('eyebrow','SIMULATION EXCHANGE') @section('heading','Currency conversion')
@section('content')
<div class="notice-banner"><span class="notice-icon">i</span><div><strong>Paper conversion only</strong><p>Conversion moves simulation credits between paper wallets. It does not access, purchase or transfer real cryptocurrency. USD and USDT use a 1:1 reference proxy; this is not a guaranteed exchange rate.</p></div><span class="mode-tag">PAPER ONLY</span></div>
<div class="content-grid"><section class="panel"><div class="panel-head"><div><span class="eyebrow">CONVERT ASSETS</span><h2>New paper conversion</h2></div></div>
<form id="conversion-form" class="form-stack"><input type="hidden" name="request_key" value="{{ (string)\Illuminate\Support\Str::uuid() }}"><input type="hidden" name="source_quote_at"><input type="hidden" name="target_quote_at">
<label>From<select name="from_currency" id="conversion-from">@foreach($currencies as $c)<option value="{{ $c }}" data-balance="{{ \App\Support\NumberFormatter::decimal($balances[$c] ?? 0) }}" @selected($c===$currency)>{{ $c }} · available {{ \App\Support\NumberFormatter::decimal($balances[$c] ?? 0) }}</option>@endforeach</select></label>
<p class="muted" id="conversion-available">Available balance: {{ \App\Support\NumberFormatter::decimal($balances[$currency] ?? 0) }} {{ $currency }}</p>
<label>To<select name="to_currency">@foreach($currencies as $c)<option value="{{ $c }}" @selected($c==='BTC')>{{ $c }}</option>@endforeach</select></label>
<label>Amount<input name="amount" type="number" min="0.00000001" step="0.00000001" max="{{ \App\Support\NumberFormatter::decimal($balances[$currency] ?? 0) }}" value="10.00000000" required></label>
<button class="button button-primary button-full" id="preview-conversion" type="button">Preview conversion</button>
<div id="conversion-preview" class="empty-state" hidden><strong>Indicative output</strong><h2 id="conversion-output">—</h2><p id="conversion-details"></p><button class="button button-primary button-full" id="confirm-conversion" type="button">Confirm paper conversion</button></div>
</form></section><section class="panel"><div class="panel-head"><div><span class="eyebrow">HOW IT WORKS</span><h2>Simulation controls</h2></div></div><div class="quick-links"><div><strong>Fresh market prices</strong><small>Both asset prices must be fresh before a preview can be generated.</small></div><div><strong>Quote lock</strong><small>If either market quote changes before confirmation, the conversion is rejected and must be previewed again.</small></div><div><strong>Double-entry accounting</strong><small>Source and destination balances are posted as balanced currency-specific ledger entries.</small></div><div><strong>No real-world settlement</strong><small>No exchange, custody, withdrawal or blockchain transfer occurs.</small></div></div></section></div>
<section class="panel"><div class="panel-head"><div><span class="eyebrow">PAPER CONVERSION RECORDS</span><h2>Recent conversions</h2></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>DATE</th><th>FROM</th><th>TO</th><th>RATE (USDT)</th><th>STATUS</th></tr></thead><tbody>
@forelse($conversions as $c)<tr><td>{{ $c->created_at }}</td><td>{{ \App\Support\NumberFormatter::decimal($c->amount_in) }} {{ $c->from_currency }}</td><td>{{ \App\Support\NumberFormatter::decimal($c->amount_out) }} {{ $c->to_currency }}</td><td>{{ \App\Support\NumberFormatter::decimal($c->source_price) }} / {{ \App\Support\NumberFormatter::decimal($c->target_price) }}</td><td><span class="table-status verified">PAPER</span></td></tr>@empty<tr><td colspan="5" class="table-empty">No paper conversions recorded.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
@push('scripts')
<script>
const conversionForm=document.getElementById('conversion-form');
const conversionPreview=document.getElementById('conversion-preview');
const previewButton=document.getElementById('preview-conversion');
const confirmButton=document.getElementById('confirm-conversion');
let conversionReady=false;
function conversionPayload(){return Object.fromEntries(new FormData(conversionForm).entries());}
function updateAvailableBalance(){
    const source=conversionForm.elements.from_currency;
    const selected=source.options[source.selectedIndex];
    const balance=selected.dataset.balance||'0';
    document.getElementById('conversion-available').textContent='Available balance: '+new Intl.NumberFormat(undefined,{maximumFractionDigits:8}).format(Number(balance))+' '+source.value;
    conversionForm.elements.amount.max=balance;
    conversionReady=false;
    conversionPreview.hidden=true;
}
conversionForm.elements.from_currency.addEventListener('change',updateAvailableBalance);
conversionForm.addEventListener('input',()=>{conversionReady=false;conversionPreview.hidden=true;});
previewButton.addEventListener('click',async()=>{
    previewButton.disabled=true;conversionReady=false;confirmButton.disabled=true;
    try{
        const quote=await window.shellAjax(@json(route('convert.quote')),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(conversionPayload())});
        conversionForm.elements.source_quote_at.value=quote.source_quote_at||'';
        conversionForm.elements.target_quote_at.value=quote.target_quote_at||'';
        document.getElementById('conversion-output').textContent=new Intl.NumberFormat(undefined,{maximumFractionDigits:8}).format(Number(quote.amount_out))+' '+quote.to_currency;
        document.getElementById('conversion-details').textContent='Indicative rate basis: '+new Intl.NumberFormat(undefined,{maximumFractionDigits:8}).format(Number(quote.source_price))+' USDT per '+quote.from_currency+' / '+new Intl.NumberFormat(undefined,{maximumFractionDigits:8}).format(Number(quote.target_price))+' USDT per '+quote.to_currency+'. Simulation only.';
        conversionPreview.hidden=false;conversionReady=true;confirmButton.disabled=false;
    }catch(error){window.shellToast(error.message,'error');conversionPreview.hidden=true;}
    finally{previewButton.disabled=false;}
});
confirmButton.addEventListener('click',async()=>{
    if(!conversionReady)return;
    confirmButton.disabled=true;
    try{
        const result=await window.shellAjax(@json(route('convert.paper')),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(conversionPayload())});
        window.shellToast(result.message);window.location.reload();
    }catch(error){window.shellToast(error.message,'error');conversionReady=false;conversionPreview.hidden=true;}
    finally{confirmButton.disabled=false;}
});
</script>
@endpush
