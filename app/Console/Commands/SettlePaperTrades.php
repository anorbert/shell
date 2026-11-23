<?php
namespace App\Console\Commands;
use App\Services\PaperTradingService;use Illuminate\Console\Command;
class SettlePaperTrades extends Command{protected $signature='shell:paper:settle';protected $description='Settle expired paper trades using fresh post-expiry quotes';public function handle(PaperTradingService $s):int{$n=$s->settleDue();$this->info("Settled {$n} paper trades. Others wait for valid quotes.");return self::SUCCESS;}}
