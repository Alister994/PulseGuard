<?php

namespace App\Providers;

<<<<<<< HEAD
use App\Events\SiteDownDetected;
use App\Events\SiteRecovered;
use App\Events\SslExpiringSoon;
use App\Listeners\SendDowntimeAlerts;
use App\Listeners\SendRecoveryAlerts;
use App\Listeners\SendSslExpiringAlerts;
use Illuminate\Support\Facades\Event;
=======
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
<<<<<<< HEAD
    /**
     * Register any application services.
     */
=======
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
    public function register(): void
    {
        //
    }

<<<<<<< HEAD
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(SiteDownDetected::class, SendDowntimeAlerts::class);
        Event::listen(SiteRecovered::class, SendRecoveryAlerts::class);
        Event::listen(SslExpiringSoon::class, SendSslExpiringAlerts::class);
=======
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
    }
}
