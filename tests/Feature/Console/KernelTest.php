<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportarEstruturaCorporativa;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    Bus::fake();
    Event::fake();
});

// scheduled time 1:00
test('schedule fora do horário, não dispara os jobs', function ($data_hora, $job) {
    testTime()->freeze($data_hora);

    $this->artisan('schedule:run');

    Event::assertNotDispatched(ScheduledTaskStarting::class);

    Bus::assertNothingDispatched();
})->with([
    ['2020-10-20 00:59:59', ImportarEstruturaCorporativa::class],
    ['2020-10-20 01:01:00', ImportarEstruturaCorporativa::class],
]);

// Caminho feliz
test('schedule no horário correto, dispara os jobs agendados', function ($data_hora, $job) {
    testTime()->freeze($data_hora);

    $this->artisan('schedule:run');

    Event::assertDispatched(ScheduledTaskFinished::class, function ($event) use ($job) {
        return strpos($event->task->description, $job) !== false;
    });

    Bus::assertDispatched($job);
})->with([
    ['2020-10-20 01:00:00', ImportarEstruturaCorporativa::class],
    ['2020-10-20 01:00:59', ImportarEstruturaCorporativa::class],
]);
