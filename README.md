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


## Trading terminal and market chart

- The client terminal displays Binance Spot OHLCV candlesticks for configured market pairs and selectable 1m, 5m, 15m, 1h, 4h and 1d intervals.
- Candle data is visual context only. The server independently checks a fresh stored ticker quote when a demo position opens and requires a fresh post-expiry ticker quote to settle it.
- The demo ticket displays the wallet denomination, configured payout percentage, quick stake amounts and an estimated total return. The estimate is informational; the server remains authoritative.
- Activity records and CSV exports use each trade's stored wallet_currency, preserving legacy USDT positions while new positions use USD.
- The conversion workspace defaults to the funded USD demo wallet and displays available balances for each provisioned demo currency.
- The first-session dashboard provides direct steps for exploring markets, reviewing wallets, opening a demo position and checking activity.

## Operational checks

- The administrator Operations screen highlights stale configured quotes, expired-but-unsettled positions, open positions and the last successful market refresh.
- Review failed job heartbeat messages and application logs when market refresh or settlement is unsuccessful. A scheduled job heartbeat is operational evidence, not a guarantee that external providers or settlement will always be available.
- Verify php artisan schedule:list during deployment and keep the single scheduler cron entry active. Laravel's withoutOverlapping scheduler locks require a functioning cache store. See the Laravel 12 task scheduling documentation: https://laravel.com/docs/12.x/scheduling.

## Manual crypto deposits, withdrawals and KYC

- Admins configure each supported crypto asset/network and the exact deposit address from **Admin → Wallet & KYC review**. Keep each network disabled until the address, memo/tag requirements and custody controls have been independently checked.
- Clients submit the transaction hash, crypto quantity sent and estimated USD value. A request remains pending and does not credit the real wallet until an admin verifies the transaction on the correct chain and confirms the final USD amount.
- Withdrawals require an approved KYC submission. The requested amount is reserved from the real wallet at submission. Rejection releases the reserve; marking paid settles the reserve and records the outbound transaction hash, actual crypto quantity sent and any USD fee.
- KYC files are encrypted with Laravel application encryption before being stored on the private local disk, and can only be decrypted and downloaded through the authenticated admin review route. Keep the Laravel APP_KEY backed up securely because it is required to decrypt KYC files; configure private, encrypted backups and a retention policy before production.
- New requests create admin in-app notifications and send email to every account with the admin role. Configure a real SMTP/mail provider in production; the development default `MAIL_MAILER=log` writes messages to the application log instead of delivering email.
- This workflow is manual recordkeeping, not blockchain verification. Admins must independently verify confirmations, token contract, network, destination address, amount, fees, and sanctions/risk controls before approval.
- Real-money trading remains disabled. Obtain legal/compliance approval and establish custody, reconciliation, KYC/AML, sanctions screening, incident response and operational controls before accepting customer funds.


### Admin KYC case review

- Admins have a dedicated **Admin → KYC review** queue, separate from wallet-request processing.
- The queue defaults to pending cases and supports filtering by pending / verified / rejected / all, plus search by client name, email, document number or case ID. Results are paginated and pending cases are prioritised.
- Each case page previews the encrypted identity document and required selfie in the browser, shows client/account metadata and previous KYC submissions, and records reviewer identity, time, decision, checklist and notes.
- A KYC submission requires an identity document, document number and selfie. Approval requires the administrator to attest that the document is legible and valid, account details match, selfie matches the portrait, duplicate checks are complete and the required risk/sanctions screening has been performed. A reviewer note is mandatory for both approval and rejection.
- The checklist is an auditable human attestation; the application does not automatically authenticate identity documents or perform sanctions screening. Admins must use the organisation's approved verification process and must not tick checks they have not performed.
- KYC case documents are served only through authenticated admin routes, with no-store response headers and an allowlist of PDF/JPEG/PNG content types.


## International client registration

- Registration presents a country/region selector covering 245 ISO region calling-code entries, sourced from libphonenumber calling-code metadata. The default selection is Rwanda (+250); users can select their own region.
- The phone prefix updates with the selected region. The server validates the region against `config/phone-countries.php`, accepts a national-format phone number, removes the domestic trunk prefix and stores the result in E.164 format in `users.phone_e164`.
- The ISO alpha-2 country/region code is stored in `users.country_code`. Existing accounts remain valid because these fields are nullable. The migration also adds `phone_verified_at` for a future verification flow.
- Client settings and the admin client profile display the stored international contact details.
- This is international-format registration, not proof of phone ownership. SMS/WhatsApp OTP, full libphonenumber per-country number-plan validation, locale/time-zone preferences, localised pricing/payment rails, and jurisdiction-specific compliance are not included. Do not use the number for account recovery or sensitive authentication until phone verification is implemented.
