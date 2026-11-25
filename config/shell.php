<?php

return [
    'market' => [
        'base_url' => env('SHELL_MARKET_BASE_URL', 'https://api.binance.com'),
        'max_age_seconds' => (int) env('SHELL_MARKET_MAX_AGE', 90),
        'symbols' => ['BTCUSDT', 'ETHUSDT', 'BNBUSDT', 'SOLUSDT', 'XRPUSDT', 'ADAUSDT', 'DOGEUSDT', 'AVAXUSDT', 'DOTUSDT', 'LTCUSDT', 'LINKUSDT', 'UNIUSDT', 'ATOMUSDT', 'XLMUSDT', 'ALGOUSDT', 'NEARUSDT', 'AAVEUSDT', 'TRXUSDT', 'ETCUSDT', 'BCHUSDT'],
    ],
    'paper' => [
        'enabled' => (bool) env('SHELL_PAPER_TRADING_ENABLED', true),
        // Real-money execution is a separate, explicit production decision.
        'real_enabled' => (bool) env('SHELL_REAL_TRADING_ENABLED', false),
        'max_stake' => env('SHELL_PAPER_MAX_STAKE', '10000.00000000'),
        'payout_basis_points' => (int) env('SHELL_PAPER_PAYOUT_BPS', 18000),
        'durations' => [60, 300, 900, 3600],
        'currency' => 'USD',
        'welcome_balance' => '20000.00000000',
        'topup_min' => '100.00000000',
        'topup_max' => '20000.00000000',
        'settlement_quote_grace_seconds' => (int) env('SHELL_SETTLEMENT_QUOTE_GRACE_SECONDS', 15),
    ],
];
