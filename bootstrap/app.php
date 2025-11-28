<?php

use App\Jobs\SystemMonitorJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // System monitoring - configurable interval
        if (config('monitoring.enabled', true)) {
            $interval = config('monitoring.interval_minutes', 2);
            $schedule->job(new SystemMonitorJob())
                ->cron("*/{$interval} * * * *")
                ->name('system-monitor')
                ->withoutOverlapping();
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
