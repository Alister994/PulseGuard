<?php

<<<<<<< HEAD
use Illuminate\Console\Scheduling\Schedule;
=======
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
<<<<<<< HEAD
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('pulseguard:dispatch-checks')->everyMinute()->withoutOverlapping(2)->runInBackground();
        $schedule->command('pulseguard:dispatch-ssl')->daily()->at('02:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
=======
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'branch_admin' => \App\Http\Middleware\EnsureBranchAdminOrSuperAdmin::class,
            'hr' => \App\Http\Middleware\EnsureHrOrAbove::class,
            'dept_manager' => \App\Http\Middleware\EnsureDepartmentManagerOrAbove::class,
            'has_branch' => \App\Http\Middleware\EnsureUserHasBranch::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
        //
    })->create();
