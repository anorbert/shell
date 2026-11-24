<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::directive('money', function ($expression) {
            return "<?php echo e(\\App\\Support\\NumberFormatter::money($expression)); ?>";
        });

        Blade::directive('decimal', function ($expression) {
            return "<?php echo e(\\App\\Support\\NumberFormatter::decimal($expression)); ?>";
        });
    }
}
