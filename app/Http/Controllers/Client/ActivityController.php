<?php
namespace App\Http\Controllers\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
class ActivityController extends Controller {
 private function query(Request $r){$v=$r->validate(['status'=>['nullable','in:open,won,lost,void'],'symbol'=>['nullable','string','max:24'],'from'=>['nullable','date'],'to'=>['nullable','date','after_or_equal:from']]);return DB::table('paper_trades')->where('user_id',$r->user()->id)->when($v['status']??null,fn($q,$x)=>$q->where('status',$x))->when($v['symbol']??null,fn($q,$x)=>$q->where('symbol',strtoupper($x)))->when($v['from']??null,fn($q,$x)=>$q->whereDate('opened_at','>=',$x))->when($v['to']??null,fn($q,$x)=>$q->whereDate('opened_at','<=',$x))->orderByDesc('opened_at');}
 public function __invoke(Request $r){$trades=$this->query($r)->paginate(20)->withQueryString();return view('client.activity',compact('trades'));}
 public function export(Request $r):StreamedResponse{$trades=$this->query($r)->get();return response()->streamDownload(function()use($trades){$out=fopen('php://output','w');fputcsv($out,['Opened at','Pair','Direction','Stake USDT','Opening price','Closing price','Status','Expires at','Settled at']);foreach($trades as $t)fputcsv($out,[$t->opened_at,$t->symbol,$t->direction,$t->stake,$t->opening_price,$t->closing_price,$t->status,$t->expires_at,$t->settled_at]);fclose($out);},'shell-paper-trading-activity.csv',['Content-Type'=>'text/csv; charset=UTF-8']);}
}