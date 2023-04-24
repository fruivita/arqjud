<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EQueue;
use App\Jobs\ImportarDadosRH;
use App\Models\Atividade;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    Queue::fake();
    Event::fake();
});

// scheduled time 1:00
test('schedule fora do horário, não envia job algum para a queue', function (string $data_hora) {
    testTime()->freeze($data_hora);

    $this->artisan('schedule:run');

    Event::assertNotDispatched(ScheduledTaskStarting::class);

    Queue::assertNothingPushed();
})->with([
    ['2020-10-20 00:59:59'],
    ['2020-10-20 01:01:00'],
]);

// Caminho feliz
test('schedule no horário correto, dispara os jobs agendados para a queue', function (string $data_hora, string $job) {
    testTime()->freeze($data_hora);

    $this->artisan('schedule:run');

    Event::assertDispatched(ScheduledTaskFinished::class, function ($event) use ($job) {
        return strpos($event->task->description, $job) !== false;
    });

    Queue::assertPushedOn(EQueue::Alta->value, $job);
})->with([
    ['2020-10-20 01:00:00', ImportarDadosRH::class],
    ['2020-10-20 01:00:59', ImportarDadosRH::class],
]);

test('schedule, desabilita o registro de atividade, não registrando o job de solicitação de importação', function () {
    testTime()->freeze('2020-10-20 01:00:00');

    $this->artisan('schedule:run');

    expect(Atividade::count())->toBe(0);
});
