# Shell

Shell is an independent Laravel application. Its active browser interface is rendered with Blade and vanilla JavaScript/AJAX.

## Source-code map

```
app/
  Console/Commands/       # Artisan commands
  Http/
    Controllers/
      Admin/               # Administrator controllers
      Client/              # Client workspace and settings controllers
      Guests/              # Public pages and authentication controllers
      Controller.php       # Shared base controller
    Middleware/            # Role and request middleware
    Requests/              # Form request validation
  Models/                  # Eloquent models
  Services/                # Market, ledger and paper-trading services
  Support/                 # Shared domain helpers

resources/views/
  admin/                   # Administrator Blade pages
  client/                  # Client Blade pages and settings
  guests/                  # Public landing and authentication pages
  layouts/                 # Shared Blade layouts

routes/
  web.php                  # Public web entry point and shared dashboard redirect
  admin.php                # Administrator-only browser routes
  client.php               # Client-only browser routes
  auth.php                 # Authentication endpoints included by web.php
  settings.php             # Authenticated account settings included by web.php
  api.php                  # Stateless API endpoints
  console.php              # Scheduled and console commands
```

## Route organization

- Public landing route stays in `routes/web.php`.
- `routes/admin.php` is protected by `auth` and the `admin` middleware.
- `routes/client.php` is protected by `auth` and the `client` middleware.
- `routes/api.php` is registered separately with Laravel's API middleware and `/api` prefix.
- Authentication and settings route files are included by `web.php`; route names and URLs are retained.

## Market data and paper trading

- Configure `SHELL_MARKET_BASE_URL` and `SHELL_MARKET_MAX_AGE`. The current adapter reads Binance Spot public ticker data for allow-listed USDT pairs.
- Run `php artisan migrate`.
- Refresh quotes with `php artisan shell:markets:refresh`.
- Run Laravel's scheduler every minute to refresh quotes and settle eligible expired paper trades.
- Issue non-withdrawable simulation credits from the server CLI: `php artisan shell:paper-credit client@example.com 1000`.

## Important limits

Live deposits, withdrawals, conversions, custody, investment products and redeemable balances are not implemented. Paper credits have no cash value. Stale or missing market quotes block new paper trades.

## Verification

Run `composer install`, `php artisan migrate`, `npm install`, `npm run build`, and `php artisan test` in a configured environment. Repository edits alone do not prove runtime verification.
