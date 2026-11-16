<?php

namespace App\Providers;

use App\Models\Bid;
use App\Models\Tender;
use App\Policies\BidPolicy;
use App\Policies\TenderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Tender::class, TenderPolicy::class);
        Gate::policy(Bid::class, BidPolicy::class);

        if (
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
            str_starts_with((string) config('app.url'), 'https://')
        ) {
            URL::forceScheme('https');
            request()->server->set('HTTPS', 'on');
        }
    }
}
