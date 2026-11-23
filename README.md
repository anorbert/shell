# Shell

Shell is an independent Laravel application. Its active browser interface is rendered with Blade and vanilla JavaScript/AJAX. It is separate from Viomia Bot and CORE.

## Source-code map

```
app/
  Console/Commands/          # Market refresh, paper settlement and credit commands
  Http/
    Controllers/
      Admin/                 # Administrator workspace and support inbox
      Auth/                  # Laravel authentication controllers
      Client/                # Client workspace, trading, conversion and support
      Client/Settings/       # Profile, password and security settings
      Guests/                # Public landing pages
      Controller.php         # Shared base controller
    Middleware/              # Role enforcement and administrator audit logging
    Requests/                # Form request validation
  Models/
  Services/                  # Market quotes, immutable ledger, paper trading/conversion
  Support/                   # Decimal helpers

resources/views/
  admin/                     # Administrator Blade pages
  auth/                      # Authentication pages
  client/                    # Client Blade pages and settings
  guests/                    # Public pages
  layouts/                   # Shared Blade layouts

routes/
  web.php                    # Public web entry point and shared dashboard redirect
  admin.php                  # Administrator-only browser routes
  client.php                 # Client-only browser routes
  auth.php                   # Authentication endpoints included by web.php
  settings.php               # Account settings included by web.php
  api.php                    # Reserved stateless API entry point
  console.php                # Scheduled and console commands
```

## Current functionality

### Client workspace
- Provider-sourced market quotes with search, price sorting and per-client favorites.
- Paper UP/DOWN trades with fresh quote checks, reserved stakes, expiry settlement and idempotent ledger postings.
- Paper conversion between the USD-denominated demo wallet, legacy USDT demo balances and supported quoted assets. USD conversion uses USDT market prices as a 1:1 reference proxy. Both legs are posted through currency-specific double-entry ledger accounts. Quote changes between preview and confirmation reject the conversion.
- Every client receives a $20,000 USD-denominated demo welcome balance once, plus an independent real wallet initialized at $0.
- Clients can add $100–$20,000 in demo credits per top-up; demo withdrawals and demo-to-real transfers are prohibited.
- Real-wallet credits are reserved for confirmed deposits through a future approved payment integration; deposits and withdrawals are not live yet.
- Portfolio balances by paper currency, activity filters and CSV export.
- In-app notifications for settled trades, paper conversions and support replies.
- Support tickets and threaded replies.
- Login history and database-session revocation after password confirmation.

### Administrator workspace
- Client account directory with name/email search.
- Read-only market and paper-trade operations monitoring.
- Recent paper conversions and administrator access audit trail.
- Support inbox with ticket replies and status updates.
- Scheduler heartbeat and background job health for market refresh and paper settlement.

## Route organization

- Public landing route stays in `routes/web.php`.
- `routes/admin.php` is protected by `auth`, `admin` and verified-email middleware.
- `routes/client.php` is protected by `auth`, `client` and verified-email middleware.
- `routes/api.php` is registered separately with Laravel's API middleware and `/api` prefix.
- Authentication and account settings route files are included by `web.php`.

## Local setup

1. Configure the database and application key.
2. Set `SESSION_DRIVER=database` if you want the security page to enumerate and revoke other sessions.
3. Configure `SHELL_MARKET_BASE_URL`, `SHELL_MARKET_MAX_AGE` and `SHELL_PAPER_TRADING_ENABLED=true`.
4. Run `composer install`, `php artisan migrate`, `npm install` and `npm run build`.
5. Refresh market quotes with `php artisan shell:markets:refresh`.
6. New clients automatically receive $20,000 in non-withdrawable USD demo credits. Additional demo credits may be added from the client wallet center; administrators can also issue non-withdrawable simulation credits from the server CLI: `php artisan shell:paper-credit client@example.com 1000`.
7. Keep Laravel's scheduler running every minute. A typical server cron entry is:

```cron
* * * * * cd /path/to/shell && php artisan schedule:run >> /dev/null 2>&1
```

The admin Operations page shows the last recorded execution status. A successful heartbeat does not prove that the server cron will continue running; monitor it externally as well.

## Financial and security boundaries

- Every demo welcome grant and demo top-up is simulated, ledger-recorded and has no cash or redemption value. Demo funds cannot be withdrawn or transferred to the real wallet.
- The real wallet starts at zero. Only a future confirmed payment-provider deposit flow may credit it; no client-side balance editing or demo top-up can do so.
- Paper conversions are internal simulations only. No exchange, custody, blockchain transfer or real asset purchase occurs.
- Real deposits, withdrawals, custody, live trading and investment-return products remain disabled.
- No guaranteed returns, fake balances or fabricated market quotes are used.
- Ledger transactions are balanced and idempotent; posted entries are not edited from the admin interface.
- Two-factor authentication is not implemented. Do not represent accounts as 2FA-protected.
- Session revocation requires the database session driver. When another session is revoked, remembered-device tokens are rotated as well.
- KYC/VIP tiers, bonuses and real-money features require approved providers, explicit product rules, legal review and security assessment before implementation or launch.

## Verification

Run `php artisan test`, `vendor/bin/pint --test`, `npm run build` and the frontend lint command in a configured environment. GitHub Actions currently runs the test, formatting and frontend lint workflows on feature branches. CI passing is not a substitute for staging tests, production MySQL concurrency testing, external security review or deployment validation.
