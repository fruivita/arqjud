<?php

namespace App\Console;

use App\Enums\Queue;
use App\Jobs\ImportarEstruturaCorporativa;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * @see https://laravel.com/docs/9.x/scheduling
 */
class Kernel extends ConsoleKernel
{
    /**
     * Define o cronograma de comandos da aplicação.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->job(new ImportarEstruturaCorporativa(), Queue::Corporativo->value)
            ->dailyAt('1:00');
    }

    /**
     * Registra os comandos para a aplicação.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
