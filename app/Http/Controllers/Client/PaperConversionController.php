<?php
namespace App\Http\Controllers\Client;
use App\Http\Controllers\Controller;
use App\Services\PaperConversionService;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class PaperConversionController extends Controller {
 public function index(Request $r,PaperConversionService $s,LedgerService $l){$l->provisionClientWallets($r->user(),config('shell.paper.currency','USD'));$conversions=DB::table('paper_conversions')->where('user_id',$r->user()->id)->latest()->limit(25)->get();return view('client.convert',['currencies'=>$s->currencies(),'conversions'=>$conversions]);}
 public function quote(Request $r,PaperConversionService $s){$v=$r->validate(['from_currency'=>['required','string','max:12'],'to_currency'=>['required','string','max:12'],'amount'=>['required','regex:/^(0|[1-9][0-9]{0,9})(\.[0-9]{1,8})?$/']]);try{return response()->json($s->quote(strtoupper($v['from_currency']),strtoupper($v['to_currency']),$v['amount']));}catch(RuntimeException|\InvalidArgumentException $e){return response()->json(['message'=>$e->getMessage()],422);}}
 public function convert(Request $r,PaperConversionService $s){$v=$r->validate(['from_currency'=>['required','string','max:12'],'to_currency'=>['required','string','max:12'],'amount'=>['required','regex:/^(0|[1-9][0-9]{0,9})(\.[0-9]{1,8})?$/'],'source_quote_at'=>['nullable','string','max:40'],'target_quote_at'=>['nullable','string','max:40'],'request_key'=>['required','uuid']]);try{$row=$s->convert($r->user(),strtoupper($v['from_currency']),strtoupper($v['to_currency']),$v['amount'],$v['source_quote_at']??'',$v['target_quote_at']??null,$v['request_key']);return response()->json(['message'=>'Paper conversion completed.','conversion'=>$row],201);}catch(RuntimeException|\InvalidArgumentException $e){return response()->json(['message'=>$e->getMessage()],422);}}
}