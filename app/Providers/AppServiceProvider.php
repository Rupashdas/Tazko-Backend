<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One per request (and per queued job), never shared between them.
        $this->app->scoped(\App\Support\CurrentWorkspace::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The reset form lives in the SPA, so the emailed link opens it there.
        ResetPassword::createUrlUsing(fn (User $user, string $token) =>
            rtrim(config('app.frontend_url'), '/')
            . '/reset-password?token=' . $token
            . '&email=' . urlencode($user->email)
        );
    }
}
