<?php
namespace App\Http\Controllers;
use App\Services\LedgerService;use App\Support\Decimal;use Illuminate\Http\Request;
class PortfolioController extends Controller{public function __invoke(Request $r,LedgerService $l){$c=config('shell.paper.currency');$w=$l->wallet($r->user(),$c);return view('client.portfolio',['currency'=>$c,'balance'=>Decimal::fromUnits($l->balanceUnits($w))]);}}
