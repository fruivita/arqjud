<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EQueue;
use App\Jobs\NotificarSolicitanteDevolucao;
use App\Models\Usuario;
use App\Notifications\ProcessoDevolvido;
use Database\Seeders\PerfilSeeder;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    testTime()->freeze();

    $this->devolucao = new \stdClass();
    $this->devolucao->processo = '1111';
    $this->devolucao->devolvido_em = now();
    $this->devolucao->solicitante = Usuario::factory()->create();
});

// Caminho feliz
test('job NotificarSolicitanteDevolucao envia notificação ao solicitante', function () {
    Notification::fake();

    NotificarSolicitanteDevolucao::dispatchSync($this->devolucao);

    Notification::assertSentTo($this->devolucao->solicitante, ProcessoDevolvido::class);
});

test('job NotificarSolicitanteDevolucao cria a notificação com todos os parâmetros e canal esperados', function () {
    Notification::fake();

    NotificarSolicitanteDevolucao::dispatchSync($this->devolucao);

    Notification::assertSentTo(
        $this->devolucao->solicitante,
        ProcessoDevolvido::class,
        function (ProcessoDevolvido $notification, $channels) {
            expect($notification->toArray(null))->toMatchArray([
                'processo' => $this->devolucao->processo,
                'devolvido_em' => now()->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'url' => route('solicitacao.index'),
            ])->and($channels)->toMatchArray(['mail']);

            return true;
        }
    );
});

test('job NotificarSolicitanteDevolucao envia para a queue de prioridade baixa a execução da notificação', function () {
    Queue::fake()->except([
        NotificarSolicitanteDevolucao::class,
    ]);

    NotificarSolicitanteDevolucao::dispatchSync($this->devolucao);

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job) => $job->notification::class === ProcessoDevolvido::class
    );

    Queue::assertPushed(SendQueuedNotifications::class, 1); // 1 por usuário
});
