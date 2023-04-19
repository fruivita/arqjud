<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EQueue;
use App\Jobs\NotificarSolicitanteProcessoDisponivel;
use App\Models\Usuario;
use App\Notifications\ProcessoDisponibilizado;
use Database\Seeders\PerfilSeeder;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    testTime()->freeze();

    $this->notificar = new \stdClass();
    $this->notificar->processo = '1111';
    $this->notificar->solicitante = Usuario::factory()->create();
});

// Caminho feliz
test('job NotificarSolicitanteProcessoDisponivel envia notificação ao solicitante', function () {
    Notification::fake();

    NotificarSolicitanteProcessoDisponivel::dispatchSync($this->notificar);

    Notification::assertSentTo($this->notificar->solicitante, ProcessoDisponibilizado::class);
});

test('job NotificarSolicitanteProcessoDisponivel cria a notificação com todos os parâmetros e canal esperados', function () {
    Notification::fake();

    NotificarSolicitanteProcessoDisponivel::dispatchSync($this->notificar);

    Notification::assertSentTo(
        $this->notificar->solicitante,
        ProcessoDisponibilizado::class,
        function (ProcessoDisponibilizado $notification, $channels) {
            expect($notification->toArray(null))->toMatchArray([
                'processo' => $this->notificar->processo,
                'url' => route('solicitacao.index'),
            ])->and($channels)->toMatchArray(['mail']);

            return true;
        }
    );
});

test('job NotificarSolicitanteProcessoDisponivel envia para a queue de prioridade baixa a execução da notificação', function () {
    Queue::fake()->except([
        NotificarSolicitanteProcessoDisponivel::class,
    ]);

    NotificarSolicitanteProcessoDisponivel::dispatchSync($this->notificar);

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job) => $job->notification::class === ProcessoDisponibilizado::class
    );

    Queue::assertPushed(SendQueuedNotifications::class, 1); // 1 por usuário
});
