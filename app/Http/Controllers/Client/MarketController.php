<?php
namespace App\Http\Controllers\Client;
use App\Http\Controllers\Controller;
use App\Services\MarketQuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class MarketController extends Controller {
 public function index(Request $r,MarketQuoteService $m){$favorites=DB::table('market_favorites')->where('user_id',$r->user()->id)->pluck('symbol')->all();return view('client.markets',['quotes'=>$m->freshQuotes(),'favorites'=>$favorites]);}
 public function data(Request $r,MarketQuoteService $m){
  $favorites=DB::table('market_favorites')->where('user_id',$r->user()->id)->pluck('symbol')->all();
  $search=strtoupper(trim((string)$r->query('search','')));$only=$r->boolean('favorites');$sort=$r->query('sort','symbol');
  $quotes=$m->freshQuotes()->filter(fn($q)=>($search===''||str_contains(strtoupper($q->symbol),$search))&&(!$only||in_array($q->symbol,$favorites,true)));
  $quotes=$sort==='price_asc'?$quotes->sortBy(fn($q)=>(float)$q->price):($sort==='price_desc'?$quotes->sortByDesc(fn($q)=>(float)$q->price):$quotes->sortBy('symbol'));
  return response()->json(['quotes'=>$quotes->values()->map(fn($q)=>['symbol'=>$q->symbol,'price'=>$q->price,'provider'=>$q->provider,'captured_at'=>$q->captured_at,'favorite'=>in_array($q->symbol,$favorites,true)]),'stale_after_seconds'=>config('shell.market.max_age_seconds')]);
 }
 public function toggleFavorite(Request $r,string $symbol){abort_unless(in_array($symbol,config('shell.market.symbols',[]),true),404);$q=DB::table('market_favorites')->where('user_id',$r->user()->id)->where('symbol',$symbol)->first();if($q){DB::table('market_favorites')->where('id',$q->id)->delete();$favorite=false;}else{DB::table('market_favorites')->insertOrIgnore(['user_id'=>$r->user()->id,'symbol'=>$symbol,'created_at'=>now(),'updated_at'=>now()]);$favorite=true;}return response()->json(['symbol'=>$symbol,'favorite'=>$favorite]);}
}