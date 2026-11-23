<?php
namespace App\Console\Commands;
use App\Services\MarketQuoteService;use Illuminate\Console\Command;use Throwable;
class RefreshMarketQuotes extends Command{protected $signature='shell:markets:refresh';protected $description='Refresh provider-sourced spot market quotes';public function handle(MarketQuoteService $s):int{try{$n=$s->refresh();$this->info("Stored {$n} provider quotes.");return self::SUCCESS;}catch(Throwable $e){report($e);$this->error('Market refresh failed; stale quotes will be blocked.');return self::FAILURE;}}}
