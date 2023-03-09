<?php

namespace App\Console;

use App\Enums\Queue;
use App\Jobs\ImportarDadosRH;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * @see https://laravel.com/docs/9.x/scheduling
 */
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->job(new ImportarDadosRH(), Queue::Alta->value)
            ->dailyAt('1:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
