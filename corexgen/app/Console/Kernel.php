<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        \App\Console\Commands\MakeServiceCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // backup shedule
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->at('01:30');

        // check company subscription 
        $schedule->command('app:subscription-check')->daily();
        $schedule->command('app:docs-check')->daily();
        //$schedule->command('queue:listen --sleep=3 --tries=3')->everyMinute();
        $schedule->command('queue:work --stop-when-empty')->everyMinute();
        $schedule->command('demo:reset-database')->hourly();

        
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
