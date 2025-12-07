<?php

use App\Jobs\CheckSslCertificates;
use App\Jobs\RenewSslCertificates;
use App\Jobs\SystemMonitorJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    
    // 1. ROUTING CONFIGURATION
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // If you need API routes for your webhook, ensure api: is defined here as well
        // api: __DIR__.'/../routes/api.php', 
    )
    
    // 2. MIDDLEWARE CONFIGURATION
    ->withMiddleware(function (Middleware $middleware): void {
        
        // FIX: Exclude the webhook path from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);

        // If you were using any specific web middleware classes, 
        // they would be appended here (this section replaces the need for the old VerifyCsrfToken.php file):
        /*
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]);
        */
    })
    
    // 3. SCHEDULING CONFIGURATION
    ->withSchedule(function (Schedule $schedule): void {
        // System monitoring - configurable interval
        if (config('monitoring.enabled', true)) {
            $interval = config('monitoring.interval_minutes', 2);
            $schedule->job(new SystemMonitorJob())
                ->cron("*/{$interval} * * * *")
                ->name('system-monitor')
                ->withoutOverlapping();
        }

        // SSL certificate renewal - runs daily at 2:30 AM
        $schedule->job(new RenewSslCertificates())
            ->dailyAt('02:30')
            ->name('ssl-renewal')
            ->withoutOverlapping();

        // SSL certificate check - runs daily at 3:00 AM (after renewal)
        $schedule->job(new CheckSslCertificates())
            ->dailyAt('03:00')
            ->name('ssl-check')
            ->withoutOverlapping();
    })
    
    // 4. EXCEPTION CONFIGURATION
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    
    // 5. APPLICATION CREATION
    ->create();
