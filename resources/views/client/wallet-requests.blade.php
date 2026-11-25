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


/* Crypto deposit selector and accessible detail modal. */
.wallet-page .crypto-deposit-intro{display:flex;align-items:center;gap:9px;flex-wrap:wrap;margin:18px 20px 13px;color:#dbe7f2;font-size:12px;font-weight:800}
.wallet-page .crypto-deposit-intro small{flex-basis:100%;padding-left:17px;color:var(--muted,#91a0b5);font-size:11px;font-weight:500}
.wallet-page .crypto-live-dot{width:8px;height:8px;border-radius:50%;background:#58d49a;box-shadow:0 0 0 4px rgba(88,212,154,.1)}
.wallet-page .crypto-method-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:0 20px 18px}
.wallet-page .crypto-method-card{position:relative;display:grid;grid-template-columns:42px minmax(0,1fr) 20px;align-items:center;gap:11px;min-width:0;padding:16px;text-align:left;border:1px solid #2d4056;border-radius:13px;background:linear-gradient(145deg,rgba(25,43,64,.92),rgba(12,23,38,.98));color:#eef4fa;cursor:pointer;transition:border-color .18s,transform .18s,background .18s}
.wallet-page .crypto-method-card:hover,.wallet-page .crypto-method-card:focus-visible{border-color:#69b8e6;background:linear-gradient(145deg,rgba(34,74,103,.55),rgba(12,23,38,.98));transform:translateY(-2px);outline:0;box-shadow:0 0 0 3px rgba(105,184,230,.12)}
.wallet-page .crypto-method-symbol{display:grid;place-items:center;width:42px;height:42px;border-radius:12px;background:rgba(105,184,230,.14);color:#8bd0f4;font-size:17px;font-weight:900}
.wallet-page .crypto-method-copy{display:grid;gap:4px;min-width:0}
.wallet-page .crypto-method-copy strong{font-size:14px;letter-spacing:.01em}
.wallet-page .crypto-method-copy small{color:#9aabc0;font-size:11px;overflow-wrap:anywhere}
.wallet-page .crypto-method-arrow{justify-self:end;color:#82c8ed;font-size:20px}
.wallet-page .crypto-method-action{grid-column:2/4;color:#82c8ed;font-size:10px;font-weight:800;letter-spacing:.02em}
.wallet-page .crypto-safety-note{display:flex;align-items:flex-start;gap:10px;margin:0 20px 20px;padding:12px 14px;border:1px solid rgba(220,173,85,.22);border-radius:11px;background:rgba(166,119,38,.08);color:#c5cfda;font-size:11px;line-height:1.6}
.wallet-page .crypto-safety-note svg{flex:0 0 18px;width:18px;height:18px;color:#e2b65e}
.wallet-page .crypto-safety-note strong{color:#f0d59b}
.wallet-page .crypto-modal[hidden]{display:none}
.wallet-page .crypto-modal{position:fixed;z-index:10000;inset:0;display:grid;place-items:center;padding:20px}
.wallet-page .crypto-modal-backdrop{position:absolute;inset:0;width:100%;height:100%;border:0;background:rgba(2,8,16,.78);backdrop-filter:blur(5px);cursor:pointer}
.wallet-page .crypto-modal-dialog{position:relative;z-index:1;display:flex;flex-direction:column;width:min(100%,560px);max-height:min(90vh,900px);overflow:hidden;border:1px solid #34485f;border-radius:18px;background:#101c2c;box-shadow:0 24px 90px rgba(0,0,0,.5)}
.wallet-page .crypto-modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:20px 22px;border-bottom:1px solid rgba(137,160,185,.16)}
.wallet-page .crypto-modal-head .eyebrow{color:#79bfe5;font-size:9px;font-weight:900;letter-spacing:.13em}
.wallet-page .crypto-modal-head h3{margin:5px 0;font-size:19px;color:#f2f6fa}
.wallet-page .crypto-modal-head p{margin:0;color:#9aabc0;font-size:11px}
.wallet-page .crypto-modal-close{width:34px;height:34px;border:1px solid #3a4d62;border-radius:9px;background:#17263a;color:#dce7f2;font-size:23px;line-height:1;cursor:pointer}
.wallet-page .crypto-modal-body{overflow-y:auto;padding:18px 22px 22px}
.wallet-page .crypto-network-summary{display:flex;align-items:center;gap:11px;margin-bottom:16px}
.wallet-page .crypto-network-summary>div{display:grid;gap:3px}
.wallet-page .crypto-network-summary>div strong{color:#f1f5f9;font-size:14px}
.wallet-page .crypto-network-summary>div small{color:#9aabc0;font-size:11px}
.wallet-page .crypto-network-badge{margin-left:auto;padding:5px 8px;border:1px solid rgba(88,212,154,.24);border-radius:99px;background:rgba(88,212,154,.08);color:#75dcae;font-size:9px;font-weight:900;letter-spacing:.08em}
.wallet-page .crypto-address-panel,.wallet-page .crypto-memo-panel{padding:14px;border:1px solid #34465c;border-radius:12px;background:#0a1422}
.wallet-page .crypto-field-heading{display:grid;gap:4px;margin-bottom:10px}
.wallet-page .crypto-field-heading strong,.wallet-page .crypto-memo-panel strong{color:#e7eef6;font-size:11px}
.wallet-page .crypto-field-heading span{color:#91a3b8;font-size:10px}
.wallet-page .crypto-address-panel code,.wallet-page .crypto-memo-panel code{display:block;margin:0 0 12px;color:#9ed9fa;font-size:12px;line-height:1.65;overflow-wrap:anywhere;word-break:break-word}
.wallet-page .crypto-address-panel .button{width:100%;min-height:39px}
.wallet-page .crypto-memo-panel{display:grid;gap:7px;margin-top:10px}
.wallet-page .crypto-instructions{margin-top:10px;padding:12px 14px;border-radius:10px;background:rgba(105,184,230,.08);color:#b9c9d9;font-size:11px;line-height:1.65;white-space:pre-line}
.wallet-page .crypto-form-title{display:grid;gap:4px;margin:18px 0 2px}
.wallet-page .crypto-form-title strong{color:#f0f4f8;font-size:13px}
.wallet-page .crypto-form-title small{color:#91a3b8;font-size:10px}
.wallet-page .crypto-deposit-form{margin:0!important}
.wallet-page .crypto-deposit-form .muted{font-size:10px}
@media(max-width:640px){.wallet-page .crypto-method-grid{grid-template-columns:1fr;margin-left:15px;margin-right:15px}.wallet-page .crypto-deposit-intro{margin-left:15px;margin-right:15px}.wallet-page .crypto-safety-note{margin-left:15px;margin-right:15px}.wallet-page .crypto-modal{padding:9px}.wallet-page .crypto-modal-dialog{max-height:94vh;border-radius:15px}.wallet-page .crypto-modal-head,.wallet-page .crypto-modal-body{padding-left:16px;padding-right:16px}}

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
        <a href="#deposit-form" class="button button-primary" id="wallet-deposit-shortcut"><svg class="nav-icon"><use href="#i-plus"/></svg> Deposit crypto</a>
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
            @php($depositMethods = $methods->where('deposits_enabled', true)->values())
            @if($depositMethods->isEmpty())
                <div class="wallet-empty"><strong>Crypto deposits are not available yet</strong><p class="muted">No deposit networks are currently enabled. Available assets will appear here as soon as the operations team configures and enables them.</p><p class="muted">Never send funds to an address from an old request or external message. Always use the address shown inside the selected deposit option.</p></div>
            @else
                <div class="crypto-deposit-intro"><span class="crypto-live-dot"></span><span>{{ $depositMethods->count() }} deposit {{ \Illuminate\Support\Str::plural('network', $depositMethods->count()) }} available</span><small>Select the exact asset and network you intend to use.</small></div>
                <div class="crypto-method-grid" aria-label="Available crypto deposit networks">
                    @foreach($depositMethods as $method)
                        <button type="button" class="crypto-method-card" data-crypto-method
                            data-method-id="{{ $method->id }}"
                            data-asset="{{ $method->asset }}"
                            data-network="{{ $method->network }}"
                            data-address="{{ $method->wallet_address }}"
                            data-memo-label="{{ $method->memo_label }}"
                            data-memo="{{ $method->memo_value }}"
                            data-instructions="{{ $method->instructions }}">
                            <span class="crypto-method-symbol" aria-hidden="true">{{ strtoupper(substr($method->asset, 0, 1)) }}</span>
                            <span class="crypto-method-copy"><strong>{{ $method->asset }}</strong><small>{{ $method->network }}</small></span>
                            <span class="crypto-method-arrow" aria-hidden="true">↗</span>
                            <span class="crypto-method-action">View deposit details</span>
                        </button>
                    @endforeach
                </div>
                <div class="crypto-safety-note"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 4.5 6v5.5c0 4.5 3.2 7.7 7.5 9.5 4.3-1.8 7.5-5 7.5-9.5V6L12 3Z"/><path d="m9 12 2 2 4-4"/></svg><span><strong>Check the network before sending.</strong> Deposits sent through the wrong network may be permanently lost. Your wallet is credited only after the transaction is verified.</span></div>
                <div class="crypto-modal" id="crypto-deposit-modal" hidden aria-hidden="true">
                    <button type="button" class="crypto-modal-backdrop" data-close-crypto-modal aria-label="Close deposit details"></button>
                    <section class="crypto-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="crypto-modal-title">
                        <div class="crypto-modal-head"><div><span class="eyebrow">SECURE DEPOSIT</span><h3 id="crypto-modal-title">Deposit crypto</h3><p id="crypto-modal-subtitle">Review the destination carefully.</p></div><button type="button" class="crypto-modal-close" data-close-crypto-modal aria-label="Close">×</button></div>
                        <div class="crypto-modal-body">
                            <div class="crypto-network-summary"><span class="crypto-method-symbol" id="crypto-modal-symbol">₿</span><div><strong id="crypto-modal-asset">Asset</strong><small id="crypto-modal-network">Network</small></div><span class="crypto-network-badge">DEPOSIT</span></div>
                            <div class="crypto-address-panel"><div class="crypto-field-heading"><strong>Receiving address</strong><span>Only send the selected asset on this network</span></div><code id="crypto-modal-address"></code><button type="button" class="button button-secondary" id="crypto-copy-address">Copy address</button></div>
                            <div class="crypto-memo-panel" id="crypto-modal-memo-wrap" hidden><strong id="crypto-modal-memo-label">Memo</strong><code id="crypto-modal-memo"></code></div>
                            <div class="crypto-instructions" id="crypto-modal-instructions" hidden></div>
                            <form method="POST" action="{{ route('wallet-requests.deposit') }}" class="form-stack crypto-deposit-form">
                                @csrf
                                <input type="hidden" name="method_id" id="crypto-modal-method-id">
                                <div class="crypto-form-title"><strong>Submit your transaction</strong><small>After sending funds, provide the transaction details below.</small></div>
                                <label>Crypto amount sent<input name="asset_amount" type="number" min="0.00000001" step="0.000000000001" placeholder="0.00" required></label>
                                <label>Estimated USD value<input name="amount_usd" type="number" min="1" step="0.00000001" placeholder="0.00" required></label>
                                <label>Transaction hash / TxID<input name="transaction_hash" maxlength="255" placeholder="Paste your transaction hash" required></label>
                                <label>Note (optional)<textarea name="client_note" maxlength="1000" placeholder="Additional information for the operations team"></textarea></label>
                                <button class="button button-primary" type="submit">Submit deposit for verification</button>
                                <p class="muted">Submitting a request does not immediately credit your balance. The operations team must verify the transaction on-chain.</p>
                            </form>
                        </div>
                    </section>
                </div>
            @endif      @endif
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
const cryptoModal=document.getElementById('crypto-deposit-modal');
const cryptoOpeners=document.querySelectorAll('[data-crypto-method]');
const cryptoCloseButtons=document.querySelectorAll('[data-close-crypto-modal]');
let cryptoPreviousFocus=null;
function closeCryptoModal(){if(!cryptoModal)return;cryptoModal.hidden=true;cryptoModal.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');cryptoPreviousFocus?.focus();}
cryptoOpeners.forEach(card=>card.addEventListener('click',()=>{
 if(!cryptoModal)return;
 cryptoPreviousFocus=document.activeElement;
 const d=card.dataset;
 document.getElementById('crypto-modal-method-id').value=d.methodId||'';
 document.getElementById('crypto-modal-asset').textContent=d.asset||'';
 document.getElementById('crypto-modal-network').textContent=d.network||'';
 document.getElementById('crypto-modal-symbol').textContent=(d.asset||'?').charAt(0).toUpperCase();
 document.getElementById('crypto-modal-title').textContent='Deposit '+(d.asset||'crypto');
 document.getElementById('crypto-modal-subtitle').textContent='Confirm the asset and network before sending funds.';
 document.getElementById('crypto-modal-address').textContent=d.address||'';
 const memoWrap=document.getElementById('crypto-modal-memo-wrap');
 memoWrap.hidden=!d.memo;
 document.getElementById('crypto-modal-memo-label').textContent=d.memoLabel||'Memo / destination tag';
 document.getElementById('crypto-modal-memo').textContent=d.memo||'';
 const instructions=document.getElementById('crypto-modal-instructions');
 instructions.hidden=!d.instructions;
 instructions.textContent=d.instructions||'';
 cryptoModal.hidden=false;cryptoModal.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');
 cryptoModal.querySelector('.crypto-modal-close')?.focus();
}));
cryptoCloseButtons.forEach(button=>button.addEventListener('click',closeCryptoModal));
document.addEventListener('keydown',event=>{if(event.key==='Escape'&&cryptoModal&&!cryptoModal.hidden)closeCryptoModal();});
document.getElementById('crypto-copy-address')?.addEventListener('click',async()=>{
 const address=document.getElementById('crypto-modal-address').textContent;
 const button=document.getElementById('crypto-copy-address');
 try{if(navigator.clipboard&&window.isSecureContext){await navigator.clipboard.writeText(address);}else{const field=document.createElement('textarea');field.value=address;field.style.position='fixed';field.style.opacity='0';document.body.appendChild(field);field.select();const copied=document.execCommand('copy');field.remove();if(!copied)throw new Error('Copy is unavailable in this browser.');}button.textContent='Address copied';window.shellToast?.('Deposit address copied.');}
 catch(error){window.shellToast?.(error.message||'Could not copy address.','error');}
});
document.getElementById('wallet-demo-topup-form')?.addEventListener('submit',async(event)=>{event.preventDefault();const form=event.currentTarget;const button=form.querySelector('button[type="submit"]');const message=document.getElementById('wallet-demo-topup-message');button.disabled=true;message.textContent='Processing demo credit…';try{const result=await window.shellAjax(@json(route('portfolio.demo.top-up')),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({amount:form.amount.value,request_key:crypto.randomUUID()})});message.textContent=result.message;window.shellToast(result.message);window.location.href=@json(route('wallet-requests.index',['account'=>'demo']));}catch(error){message.textContent=error.message;window.shellToast(error.message,'error');}finally{button.disabled=false;}});
</script>
@endpush
