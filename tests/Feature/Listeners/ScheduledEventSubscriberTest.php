<?php

/**
 * @see https://pestphp.com/docs/
 */

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use function Spatie\PestPluginTestTime\testTime;

// Caminho feliz
test('registra em log o início e o fim da tarefa dispachada pelo schedule', function () {
    Bus::fake();
    Log::spy();

    testTime()->freeze('2020-10-20 01:00:00');

    $this->artisan('schedule:run');

    Log::shouldHaveReceived('log')
    ->withArgs(fn ($level, $message) => $level === 'notice' && $message === 'ScheduledTaskStarting')
    ->once();
    Log::shouldHaveReceived('log')
    ->withArgs(fn ($level, $message) => $level === 'notice' && $message === 'ScheduledTaskFinished')
    ->once();
});
