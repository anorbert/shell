<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class OperationsController extends Controller {
 public function __invoke(){
  $freshAfter=now()->subSeconds((int)config('shell.market.max_age_seconds',90));
  return view('admin.operations',[
   'quotes'=>DB::table('market_quotes')->orderBy('symbol')->get()->map(function($q)use($freshAfter){$q->is_fresh=$q->captured_at >= $freshAfter;return $q;}),
   'openTrades'=>DB::table('paper_trades')->join('users','users.id','=','paper_trades.user_id')->where('paper_trades.status','open')->select('paper_trades.*','users.name as client_name','users.email as client_email')->orderBy('paper_trades.expires_at')->paginate(25,['*'],'open_page'),
   'recentSettlements'=>DB::table('paper_trades')->join('users','users.id','=','paper_trades.user_id')->whereIn('paper_trades.status',['won','lost','void'])->select('paper_trades.*','users.name as client_name','users.email as client_email')->latest('paper_trades.settled_at')->limit(30)->get(),
   'jobRuns'=>DB::table('system_job_runs')->orderBy('job_key')->get(),
   'recentConversions'=>DB::table('paper_conversions')->join('users','users.id','=','paper_conversions.user_id')->select('paper_conversions.*','users.name as client_name')->latest('paper_conversions.created_at')->limit(30)->get(),
   'auditLogs'=>DB::table('admin_audit_logs')->leftJoin('users','users.id','=','admin_audit_logs.actor_user_id')->select('admin_audit_logs.*','users.name as actor_name','users.email as actor_email')->latest('admin_audit_logs.created_at')->paginate(25,['*'],'audit_page')
  ]);
 }
}