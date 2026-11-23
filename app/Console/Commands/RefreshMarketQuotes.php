<?php
namespace App\Console\Commands;
use App\Services\MarketQuoteService;use Illuminate\Console\Command;use Illuminate\Support\Facades\DB;use Throwable;
class RefreshMarketQuotes extends Command {
 protected $signature='shell:markets:refresh';protected $description='Refresh provider-sourced spot market quotes';
 public function handle(MarketQuoteService $service):int {
  $started=hrtime(true);$now=now();DB::table('system_job_runs')->updateOrInsert(['job_key'=>'market_quotes_refresh'],['status'=>'running','last_started_at'=>$now,'message'=>'Refreshing provider market quotes','created_at'=>$now,'updated_at'=>$now]);
  try{$count=$service->refresh();$duration=(int)((hrtime(true)-$started)/1000000);DB::table('system_job_runs')->where('job_key','market_quotes_refresh')->update(['status'=>'success','last_finished_at'=>now(),'last_success_at'=>now(),'duration_ms'=>$duration,'message'=>"Stored {$count} provider quotes.",'updated_at'=>now()]);$this->info("Stored {$count} provider quotes.");return self::SUCCESS;}
  catch(Throwable $e){report($e);$duration=(int)((hrtime(true)-$started)/1000000);DB::table('system_job_runs')->where('job_key','market_quotes_refresh')->update(['status'=>'failed','last_finished_at'=>now(),'duration_ms'=>$duration,'message'=>substr($e->getMessage(),0,255),'updated_at'=>now()]);$this->error('Market refresh failed; stale quotes will be blocked.');return self::FAILURE;}
 }
}