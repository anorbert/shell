<?php

namespace App\\Console\\Commands;

use App\\Services\\PaperTradingService;
use Illuminate\\Console\\Command;
use Illuminate\\Support\\Facades\\DB;
use Throwable;

class SettlePaperTrades extends Command
{
    protected $signature = 'shell:paper:settle';

    protected $description = 'Settle expired paper trades using the first verified post-expiry market quote';

    public function handle(PaperTradingService $service): int
    {
        $started = hrtime(true);
        $now = now();

        DB::table('system_job_runs')->updateOrInsert(
            ['job_key' => 'paper_trade_settlement'],
            [
                'status' => 'running',
                'last_started_at' => $now,
                'message' => 'Checking and settling expired paper trades',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        try {
            $count = $service->settleDue();
            $duration = (int) ((hrtime(true) - $started) / 1000000);
            $hasDue = DB::table('paper_trades')
                ->where('status', 'open')
                ->where('expires_at', '<=', now())
                ->exists();

            DB::table('system_job_runs')
                ->where('job_key', 'paper_trade_settlement')
                ->update([
                    'status' => 'success',
                    'last_finished_at' => now(),
                    'last_success_at' => now(),
                    'duration_ms' => $duration,
                    'message' => $hasDue
                        ? "Settled {$count} trades; remaining expired trades are waiting for a verified post-expiry quote and will be retried."
                        : "Settlement check complete; settled {$count} expired trades.",
                    'updated_at' => now(),
                ]);

            $this->info("Settled {$count} expired paper trades.");

            if ($hasDue) {
                $this->warn('Some expired trades are awaiting a verified post-expiry quote; they remain queued for the next run.');
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $duration = (int) ((hrtime(true) - $started) / 1000000);

            DB::table('system_job_runs')
                ->where('job_key', 'paper_trade_settlement')
                ->update([
                    'status' => 'failed',
                    'last_finished_at' => now(),
                    'duration_ms' => $duration,
                    'message' => substr($exception->getMessage(), 0, 255),
                    'updated_at' => now(),
                ]);

            $this->error('Paper trade settlement failed; the next scheduler run will retry.');

            return self::FAILURE;
        }
    }
}
