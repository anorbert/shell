<?php
namespace App\Http\Controllers;
use App\Services\LedgerService;use App\Services\PaperTradingService;use App\Support\Decimal;use Illuminate\Http\Request;use Illuminate\Support\Str;use Illuminate\Support\Facades\DB;use Throwable;
class PaperTradingController extends Controller{
public function index(Request $r,LedgerService $l){$w=$l->wallet($r->user(),config('shell.paper.currency'));return view('client.trading',['balance'=>Decimal::fromUnits($l->balanceUnits($w)),'trades'=>DB::table('paper_trades')->where('user_id',$r->user()->id)->latest()->paginate(12),'symbols'=>config('shell.market.symbols'),'durations'=>config('shell.paper.durations')]);}
public function store(Request $r,PaperTradingService $s){$v=$r->validate(['symbol'=>['required','string','max:24'],'direction'=>['required','in:UP,DOWN'],'stake'=>['required','regex:/^(0|[1-9][0-9]{0,9})(\.[0-9]{1,8})?$/'],'duration'=>['required','integer'],'request_key'=>['nullable','string','max:100']]);try{$id=$s->open($r->user(),strtoupper($v['symbol']),$v['direction'],$v['stake'],(int)$v['duration'],$v['request_key']?:Str::uuid()->toString());return response()->json(['message'=>'Paper trade opened.','trade_id'=>$id],201);}catch(Throwable $e){return response()->json(['message'=>$e->getMessage()],422);}}
}
