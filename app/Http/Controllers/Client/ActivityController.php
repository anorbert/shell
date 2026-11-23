<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;use Illuminate\Support\Facades\DB;
class ActivityController extends Controller{public function __invoke(Request $r){$trades=DB::table('paper_trades')->where('user_id',$r->user()->id)->latest()->paginate(20);return view('client.activity',compact('trades'));}}
