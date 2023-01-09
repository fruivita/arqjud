<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EQueue;
use App\Jobs\NotificarSolicitanteCancelamento;
use App\Models\Lotacao;
use App\Models\Usuario;
use App\Notifications\SolicitacaoCancelada;
use Database\Seeders\PerfilSeeder;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    testTime()->freeze();

    $this->solicitacao = new stdClass();
    $this->solicitacao->processo = '11111111111111111111';
    $this->solicitacao->solicitante = Usuario::factory()->create();
    $this->solicitacao->destino = Lotacao::factory()->create();
    $this->solicitacao->solicitada_em = now();
    $this->solicitacao->operador = Usuario::factory()->create();
    $this->solicitacao->cancelada_em = now();
});

// Caminho feliz
test('job NotificarSolicitanteCancelamento envia notificação ao usuário solicitante', function () {
    Notification::fake();

    NotificarSolicitanteCancelamento::dispatchSync($this->solicitacao);

    Notification::assertTimesSent(1, SolicitacaoCancelada::class); // @phpstan-ignore-line
    Notification::assertSentTo($this->solicitacao->solicitante, SolicitacaoCancelada::class);
});

test('job NotificarSolicitanteCancelamento cria a notificação com todos os parâmetros e canal esperados', function () {
    Notification::fake();

    NotificarSolicitanteCancelamento::dispatchSync($this->solicitacao);

    Notification::assertSentTo(
        $this->solicitacao->solicitante,
        SolicitacaoCancelada::class,
        function (SolicitacaoCancelada $notification, $channels) {
            expect($notification->toArray(null))->toMatchArray([
                'processo' => $this->solicitacao->processo,
                'solicitante' => $this->solicitacao->solicitante->nome,
                'destino' => $this->solicitacao->destino->nome,
                'solicitada_em' => $this->solicitacao->solicitada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'operador' => $this->solicitacao->operador->nome,
                'cancelada_em' => $this->solicitacao->cancelada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'url' => route('solicitacao.index'),
            ])->and($channels)->toMatchArray(['mail']);

            return true;
        }
    );
});

test('job NotificarSolicitanteCancelamento envia para a queue de prioridade baixa a execução da notificação', function () {
    Queue::fake()->except([
        NotificarSolicitanteCancelamento::class,
    ]);

    NotificarSolicitanteCancelamento::dispatchSync($this->solicitacao);

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job) => $job->notification::class === SolicitacaoCancelada::class
    );

    Queue::assertPushed(SendQueuedNotifications::class, 1);
});
