<?php
namespace App\Http\Controllers\Client;
use App\Http\Controllers\Controller;
use App\Services\LedgerService;
use App\Support\Decimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PortfolioController extends Controller {
 public function __invoke(Request $r,LedgerService $ledger){
  $accounts=DB::table('ledger_accounts')->where('owner_key','USER:'.$r->user()->id)->where('code','PAPER_WALLET')->orderBy('currency')->get();
  $balances=$accounts->map(fn($a)=>['currency'=>$a->currency,'balance'=>Decimal::fromUnits($ledger->balanceUnits((int)$a->id))])->filter(fn($a)=>Decimal::units($a['balance'])>0)->values();
  $currency=config('shell.paper.currency');$wallet=$ledger->wallet($r->user(),$currency);
  return view('client.portfolio',['currency'=>$currency,'balance'=>Decimal::fromUnits($ledger->balanceUnits($wallet)),'balances'=>$balances]);
 }
}