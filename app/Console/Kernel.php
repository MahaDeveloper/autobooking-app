<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('pre:booked')->everyMinute();
        $schedule->command('second:search')->everyMinute();
        $schedule->command('missed:ride')->everyMinute();
        $schedule->command('pay:tax')->hourly();
        $schedule->command('subscription:end')->daily();
        $schedule->command('take:ride')->hourly();
        $schedule->command('subscription:alert')->daily();
        $schedule->command('free:subscription')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
