<?php
namespace App\Http\Controllers;
use App\Services\MarketQuoteService;
class MarketController extends Controller{public function index(MarketQuoteService $s){return view('client.markets',['quotes'=>$s->freshQuotes()]);}public function data(MarketQuoteService $s){return response()->json(['quotes'=>$s->freshQuotes()->map(fn($q)=>['symbol'=>$q->symbol,'price'=>$q->price,'provider'=>$q->provider,'captured_at'=>$q->captured_at]),'stale_after_seconds'=>config('shell.market.max_age_seconds')]);}}
