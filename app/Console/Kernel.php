<?php

namespace App\Console;

use App\Console\Commands\FetchPortfolio;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule 'fetch:failed-popular-items' to start at 11:30 AM and run every minute after
        $schedule->command('fetch:failed-popular-items')->cron('*/1 11 * * *'); // Runs every minute from 11:30 AM to 11:59 AM

        // Schedule 'fetch:popular-items' to start at 10:00 AM and run every minute after
        $schedule->command('fetch:popular-items')->cron('*/1 10-23 * * *'); // From 10:00 AM to 11:59 PM

        // Schedule 'portfolio:fetch' to start at 12:00 PM and run every minute after
        $schedule->command('portfolio:fetch')->cron('*/1 12-23 * * *'); // From 12:00 PM to 11:59 PM

        // Schedule 'daily:task' to run at 01:00 AM daily
        $schedule->command('daily:task')->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
