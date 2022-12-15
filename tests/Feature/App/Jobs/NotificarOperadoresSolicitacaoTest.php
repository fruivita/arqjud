<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EQueue;
use App\Jobs\NotificarOperadoresSolicitacao;
use App\Models\Perfil;
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

    $this->perfil_operador = Perfil::firstWhere('slug', Perfil::OPERADOR);
    $this->operadores = Usuario::factory(3)->for($this->perfil_operador, 'perfil')->create();

    $this->solicitacao = new \stdClass();
    $this->solicitacao->processos = ['1111', '2222'];
    $this->solicitacao->solicitante = Usuario::factory()->comNome()->create();
});

// Caminho feliz
test('job NotificarOperadoresSolicitacao envia notificação a todos os usuários de perfil operador', function () {
    $nao_operadores = Usuario::factory(2)->create();

    Notification::fake();

    NotificarOperadoresSolicitacao::dispatchSync($this->solicitacao);

    Notification::assertSentTo($this->operadores, ProcessoSolicitado::class);
    Notification::assertNotSentTo($nao_operadores, ProcessoSolicitado::class);
});

test('job NotificarOperadoresSolicitacao cria a notificação com todos os parâmetros e canal esperados', function () {
    Notification::fake();

    NotificarOperadoresSolicitacao::dispatchSync($this->solicitacao);

    Notification::assertSentTo(
        $this->operadores,
        ProcessoSolicitado::class,
        function (ProcessoSolicitado $notification, $channels) {
            expect($notification->toArray(null))->toBe([
                'processos' => $this->solicitacao->processos,
                'solicitante' => $this->solicitacao->solicitante->nome,
                'lotacao_destinataria' => $this->solicitacao->solicitante->lotacao->nome,
                'solicitada_em' => now()->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'url' => 'rota',
            ])->and($channels)->toBe(['mail']);

            return true;
        }
    );
});

test('job NotificarOperadoresSolicitacao envia para a queue de prioridade baixa a execução da notificação', function () {
    Queue::fake()->except([
        NotificarOperadoresSolicitacao::class,
    ]);

    NotificarOperadoresSolicitacao::dispatchSync($this->solicitacao);

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job) => $job->notification::class === ProcessoSolicitado::class
    );

    Queue::assertPushed(SendQueuedNotifications::class, 3); // 1 por usuário
});
