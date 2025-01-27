<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule 'fetch:failed-popular-items' to run on Saturday at 12:00 PM
        // $schedule->command('fetch:failed-popular-items')->cron('0 12 * * 6'); // Saturday at 12:00 PM

        // Schedule 'fetch:popular-items' to run from Monday to Friday at 12:00 PM and then every minute until 11:59 PM
        $schedule->command('fetch:popular-items')->cron('*/1 12-23 * * 1-5'); // Monday to Friday, 12:00 PM - 11:59 PM

        // Schedule 'portfolio:fetch' to run from Monday to Friday at 12:00 AM and then every minute until 11:59 PM
        $schedule->command('portfolio:fetch')->cron('*/1 0-23 * * 1-5'); // Monday to Friday, 12:00 AM - 11:59 PM

        // Schedule 'daily:task' to run on Sunday at 12:00 PM
        $schedule->command('daily:task')->cron('0 12 * * 0'); // Sunday at 12:00 PM
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
