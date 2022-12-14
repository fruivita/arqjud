<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EQueue;
use App\Events\ProcessoSolicitadoPeloUsuario;
use App\Listeners\SolicitacaoSubscriber;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Usuario;
use App\Notifications\ProcessoSolicitado;
use Database\Seeders\PerfilSeeder;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Queue;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    testTime()->freeze();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('handleProcessoSolicitadoPeloUsuario envia para a queue todos os dados necessários para a notificação', function () {
    $operador = Perfil::firstWhere('slug', Perfil::OPERADOR);

    Usuario::factory(3)->for($operador, 'perfil')->create();

    $solicitante = Usuario::factory()->comNome()->create();

    $solicitacao = new \stdClass();
    $solicitacao->processos = ['1111', '2222'];
    $solicitacao->solicitante = $solicitante;

    $event = new ProcessoSolicitadoPeloUsuario($solicitacao);
    $listener = new SolicitacaoSubscriber();

    Queue::fake();

    $listener->handleProcessoSolicitadoPeloUsuario($event);

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        SendQueuedNotifications::class,
        function (SendQueuedNotifications $job) use ($solicitante) {
            $notification = $job->notification;
            expect($notification)->toBeInstanceOf(ProcessoSolicitado::class)
                ->and($notification->detalhes->toArray())->toBe([ // @phpstan-ignore-line
                    'processos' => ['1111', '2222'],
                    'solicitante' => $solicitante->nome,
                    'lotacao_destinataria' => $solicitante->lotacao->nome,
                    'solicitada_em' => now()->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                    'url' => 'rota',
                ]);

            return true;
        }
    );
});

test('handleProcessoSolicitadoPeloUsuario envia para a queue a notificação de todos os usuários com o perfil operador', function () {
    $operador = Perfil::firstWhere('slug', Perfil::OPERADOR);

    Usuario::factory(3)->for($operador, 'perfil')->create();

    $solicitacao = new \stdClass();
    $solicitacao->processos = ['1111', '2222'];
    $solicitacao->solicitante = Usuario::factory()->create();

    $event = new ProcessoSolicitadoPeloUsuario($solicitacao);
    $listener = new SolicitacaoSubscriber();

    Queue::fake();

    $listener->handleProcessoSolicitadoPeloUsuario($event);

    Queue::assertPushed(SendQueuedNotifications::class, 3); // 1 por usuário
});

// test('handleRemessaSolicitadaPeloArquivo envia para a queue todos os dados necessários para a notificação', function () {
//     $lotacao = Lotacao::factory()->create();
//     $solicitante = Usuario::factory()->comNome()->create();
//     actingAs($solicitante);

//     $event = new RemessaSolicitadaPeloArquivo(['1111', '2222'], $solicitante->id, $lotacao->id);
//     $listener = new RemessaSubscriber();

//     Queue::fake();

//     $listener->handleRemessaSolicitadaPeloArquivo($event);

//     Queue::assertPushedOn(
//         EQueue::Email->value,
//         SendQueuedNotifications::class,
//         function (SendQueuedNotifications $job) use ($solicitante, $lotacao) {
//             $notification = $job->notification;
//             expect($notification)->toBeInstanceOf(RemessaSolicitada::class)
//                 ->and($notification->detalhes->toArray())->toBe([ // @phpstan-ignore-line
//                     'processos' => ['1111', '2222'],
//                     'solicitante' => $solicitante->nome,
//                     'lotacao_destinataria' => $lotacao->nome,
//                     'solicitada_em' => now()->format('d-m-Y H:i:s'),
//                     'url' => route('remessa.index'),
//                 ]);

//             return true;
//         }
//     );
// });

// test('handleRemessaSolicitadaPeloArquivo envia para a queue a notificação do usuário solicitante', function () {
//     $solicitante = Usuario::factory()->create();

//     $event = new RemessaSolicitadaPeloArquivo(['1111', '2222'], $solicitante->id, $solicitante->lotacao_id);
//     $listener = new RemessaSubscriber();

//     Queue::fake();

//     $listener->handleRemessaSolicitadaPeloArquivo($event);

//     Queue::assertPushed(SendQueuedNotifications::class, 1);
// });

// test('handleRemessaEntregue envia para a queue todos os dados necessários para a notificação', function () {
//     $remessa = Remessa::factory()->create();

//     $event = new RemessaEntregue($remessa->id, ['foo@bar.com', 'bar@baz.com']);
//     $listener = new RemessaSubscriber();

//     Queue::fake();

//     $listener->handleRemessaEntregue($event);

//     Queue::assertPushedOn(
//         EQueue::Email->value,
//         SendQueuedNotifications::class,
//         function (SendQueuedNotifications $job) use ($remessa) {
//             /** @var \App\Notifications\RemessaEntregue */
//             $notification = $job->notification;

//             expect($notification)->toBeInstanceOf(NotificacaoRemessaEntregue::class)
//                 ->and($notification->detalhes->toArray())->toBe([
//                     'guia_numero' => $remessa->guia->paraHumano,
//                     'processos' => $remessa->guia->processos->toArray(),
//                     'recebedor' => $remessa->guia->recebedor['nome'],
//                     'lotacao_destinataria' => $remessa->guia->lotacao_destinataria['nome'],
//                     'entregue_em' => $remessa->guia->gerada_em->format('d-m-Y H:i:s'),
//                     'remessa_por_guia' => $remessa->remessa_por_guia,
//                     'url' => route('remessa.index'),
//                 ])
//                 ->and($notification->email_terceiros)->toBe(['foo@bar.com', 'bar@baz.com']);

//             return true;
//         }
//     );
// });

// test('handleRemessaEntregue envia para a queue a notificação do usuário recebedor', function () {
//     $remessa = Remessa::factory()->create();
//     $remessa->load('recebedor');

//     $event = new RemessaEntregue($remessa->id, []);
//     $listener = new RemessaSubscriber();

//     Queue::fake();

//     $listener->handleRemessaEntregue($event);

//     Queue::assertPushed(SendQueuedNotifications::class, 1);
// });

// test('handleRemessaDevolvida envia para a queue todos os dados necessários para a notificação', function () {
//     $remessa = Remessa::factory()->create();

//     $event = new RemessaDevolvida($remessa);
//     $listener = new RemessaSubscriber();

//     Queue::fake();

//     $listener->handleRemessaDevolvida($event);

//     Queue::assertPushedOn(
//         EQueue::Email->value,
//         SendQueuedNotifications::class,
//         function (SendQueuedNotifications $job) use ($remessa) {
//             /** @var \App\Notifications\RemessaDevolvida */
//             $notification = $job->notification;

//             expect($notification)->toBeInstanceOf(NotificacaoRemessaDevolvida::class)
//                 ->and($notification->detalhes->toArray())->toBe([
//                     'processo' => $remessa->processo->numero,
//                     'solicitante' => $remessa->solicitante->nome,
//                     'lotacao_destinataria' => $remessa->guia->lotacao_destinataria['nome'],
//                     'solicitada_em' => $remessa->solicitada_em->format('d-m-Y H:i:s'),
//                     'devolvida_em' => $remessa->devolvida_em->format('d-m-Y H:i:s'),
//                     'url' => route('remessa.index'),
//                 ]);

//             return true;
//         }
//     );
// });

// test('handleRemessaDevolvida envia para a queue a notificação do usuário recebedor', function () {
//     $remessa = Remessa::factory()->create();
//     $remessa->load('recebedor');

//     $event = new RemessaDevolvida($remessa);
//     $listener = new RemessaSubscriber();

//     Queue::fake();

//     $listener->handleRemessaDevolvida($event);

//     Queue::assertPushed(SendQueuedNotifications::class, 1);
// });
