# Shell

Shell is an independent Laravel application for a client-facing market and trading workspace with a separate administrator console.

## Current foundation

- Laravel 12 / PHP 8.2+
- Blade-rendered public, authentication, client and administrator pages
- Small vanilla JavaScript layer for AJAX requests, toast feedback and modal behaviour
- Exactly two account roles: `client` and `admin`
- Public registration always creates a client account
- Administrator access is granted only from the server/CLI
- Financial modules are deliberately unavailable until their data providers, ledger rules, compliance controls and tests are implemented

## Setup

1. Configure the database and mail transport in `.env`.
2. Run `composer install`.
3. Run `npm install`.
4. Run `php artisan migrate`.
5. Run `npm run build`.
6. Create a client account through the public registration page.
7. Grant administrator access to an existing account with `php artisan shell:grant-admin admin@example.com` and confirm the prompt.

## Important operating boundary

The current dashboard is not a live exchange. Market data, live execution, deposits, withdrawals, conversions and investment returns are not enabled. Never use real customer funds until provider approval, regulatory review, custody design, immutable double-entry accounting, reconciliation, security review and end-to-end tests are complete.
