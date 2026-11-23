<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;use Illuminate\Support\Facades\Http;use RuntimeException;
class MarketQuoteService {
public function refresh():int{$r=Http::acceptJson()->timeout(12)->get(rtrim(config('shell.market.base_url'),'/').'/api/v3/ticker/price');if(!$r->successful()||!is_array($r->json()))throw new RuntimeException('Market provider returned invalid ticker data.');$allowed=array_flip(config('shell.market.symbols',[]));$time=now();$n=0;foreach($r->json() as $item){$s=$item['symbol']??null;$p=$item['price']??null;if(!$s||!isset($allowed[$s])||!is_numeric($p)||(float)$p<=0)continue;DB::table('market_quotes')->updateOrInsert(['symbol'=>$s],['price'=>$p,'provider'=>'Binance Spot','captured_at'=>$time,'created_at'=>$time,'updated_at'=>$time]);$n++;}return $n;}
public function freshQuotes(){return DB::table('market_quotes')->where('captured_at','>=',now()->subSeconds((int)config('shell.market.max_age_seconds',90)))->whereIn('symbol',config('shell.market.symbols',[]))->orderBy('symbol')->get();}
public function freshQuote(string $s):object{$q=DB::table('market_quotes')->where('symbol',$s)->first();if(!$q||now()->diffInSeconds($q->captured_at)>(int)config('shell.market.max_age_seconds',90))throw new RuntimeException('No fresh market quote is available for this pair. Trading is blocked.');return $q;}
}
