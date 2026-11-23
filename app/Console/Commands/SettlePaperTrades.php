<?php
namespace App\Console\Commands;
use App\Services\PaperTradingService;use Illuminate\Console\Command;use Illuminate\Support\Facades\DB;use Throwable;
class SettlePaperTrades extends Command {
 protected $signature='shell:paper:settle';protected $description='Settle expired paper trades using fresh post-expiry quotes';
 public function handle(PaperTradingService $service):int {
  $started=hrtime(true);$now=now();DB::table('system_job_runs')->updateOrInsert(['job_key'=>'paper_trade_settlement'],['status'=>'running','last_started_at'=>$now,'message'=>'Checking expired paper trades','created_at'=>$now,'updated_at'=>$now]);
  try{$count=$service->settleDue();$duration=(int)((hrtime(true)-$started)/1000000);DB::table('system_job_runs')->where('job_key','paper_trade_settlement')->update(['status'=>'success','last_finished_at'=>now(),'last_success_at'=>now(),'duration_ms'=>$duration,'message'=>"Settled {$count} paper trades; trades without a fresh post-expiry quote remain open.",'updated_at'=>now()]);$this->info("Settled {$count} paper trades. Others wait for valid quotes.");return self::SUCCESS;}
  catch(Throwable $e){report($e);$duration=(int)((hrtime(true)-$started)/1000000);DB::table('system_job_runs')->where('job_key','paper_trade_settlement')->update(['status'=>'failed','last_finished_at'=>now(),'duration_ms'=>$duration,'message'=>substr($e->getMessage(),0,255),'updated_at'=>now()]);$this->error('Paper settlement job failed.');return self::FAILURE;}
 }
}