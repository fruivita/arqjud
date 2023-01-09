<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EQueue;
use App\Jobs\NotificarSolicitanteSolicitacao;
use App\Models\Lotacao;
use App\Models\Usuario;
use App\Notifications\ProcessoSolicitado;
use Database\Seeders\PerfilSeeder;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    testTime()->freeze();

    $this->solicitacao = new \stdClass();
    $this->solicitacao->processos = ['1111', '2222'];
    $this->solicitacao->solicitante = Usuario::factory()->create();
    $this->solicitacao->destino = Lotacao::factory()->create();
    $this->solicitacao->solicitada_em = now();
});

// Caminho feliz
test('job NotificarSolicitanteSolicitacao envia notificação ao solicitante', function () {
    Notification::fake();

    NotificarSolicitanteSolicitacao::dispatchSync($this->solicitacao);

    Notification::assertSentTo($this->solicitacao->solicitante, ProcessoSolicitado::class);
});

test('job NotificarSolicitanteSolicitacao cria a notificação com todos os parâmetros e canal esperados', function () {
    Notification::fake();

    NotificarSolicitanteSolicitacao::dispatchSync($this->solicitacao);

    Notification::assertSentTo(
        $this->solicitacao->solicitante,
        ProcessoSolicitado::class,
        function (ProcessoSolicitado $notification, $channels) {
            expect($notification->toArray(null))->toMatchArray([
                'processos' => $this->solicitacao->processos,
                'solicitante' => $this->solicitacao->solicitante->nome,
                'destino' => $this->solicitacao->destino->nome,
                'solicitada_em' => now()->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'url' => route('solicitacao.index'),
            ])->and($channels)->toMatchArray(['mail']);

            return true;
        }
    );
});

test('job NotificarSolicitanteSolicitacao envia para a queue de prioridade baixa a execução da notificação', function () {
    Queue::fake()->except([
        NotificarSolicitanteSolicitacao::class,
    ]);

    NotificarSolicitanteSolicitacao::dispatchSync($this->solicitacao);

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job) => $job->notification::class === ProcessoSolicitado::class
    );

    Queue::assertPushed(SendQueuedNotifications::class, 1); // 1 por usuário
});
