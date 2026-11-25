<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
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

        // Sign only the path and query so localhost and 127.0.0.1 aliases
        // do not invalidate an otherwise genuine verification link.
        VerifyEmail::createUrlUsing(function ($notifiable): string {
            $relativeUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ],
                absolute: false
            );

            return url($relativeUrl);
        });
    }
}
