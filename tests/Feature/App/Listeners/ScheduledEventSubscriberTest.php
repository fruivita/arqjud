<?php

/**
 * @see https://pestphp.com/docs/
 */

use Illuminate\Support\Facades\Log;
use function Spatie\PestPluginTestTime\testTime;

// Caminho feliz
test('registra em log o início e o fim da tarefa dispachada pelo schedule', function () {
    Log::spy();

    testTime()->freeze('2020-10-20 01:00:00');

    $this->artisan('schedule:run');

    Log::shouldHaveReceived('notice')
        ->withArgs(fn ($message) => $message === 'ScheduledTaskStarting')
        ->once();
    Log::shouldHaveReceived('notice')
        ->withArgs(fn ($message) => $message === 'ScheduledTaskFinished')
        ->once();
});
