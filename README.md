# Shell

Shell is an independent Laravel trading-platform project. The active customer and administrator interface is Blade-rendered with vanilla JavaScript/AJAX.

## Market data and paper trading

- Configure `SHELL_MARKET_BASE_URL` and `SHELL_MARKET_MAX_AGE` in the environment. The current adapter reads Binance Spot public ticker data for the allow-listed USDT pairs in `config/shell.php`.
- Run `php artisan migrate`.
- Refresh quotes manually with `php artisan shell:markets:refresh`.
- Start the Laravel scheduler in production (for example, configure cron to run `php artisan schedule:run` every minute). The scheduler refreshes market quotes and settles eligible expired paper trades.
- Issue non-withdrawable simulation credits only from the server CLI: `php artisan shell:paper-credit client@example.com 1000`.
- Paper trading is not real-money trading. The ledger stores balanced entries and posted transactions are not edited or deleted by application workflows.

## Important limits

Live deposits, withdrawals, conversions, custody, investment products and redeemable balances are not implemented. Paper credits have no cash value. Market prices are provider-sourced and trades are blocked when a quote is stale or missing. A provider outage leaves expired paper trades open until a valid post-expiry quote is available.

## Verification

Run `composer install`, `php artisan migrate`, `npm install`, `npm run build`, and `php artisan test` in a configured local environment. Repository edits alone do not prove the application is runtime-tested.
