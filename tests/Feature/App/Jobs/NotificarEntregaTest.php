<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EQueue;
use App\Jobs\NotificarEntrega;
use App\Models\Guia;
use App\Models\Usuario;
use App\Notifications\ProcessoEntregue;
use Database\Seeders\PerfilSeeder;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);
    testTime()->freeze();

    $this->entrega = new \stdClass();
    $this->entrega->recebedor = Usuario::factory()->create();
    $this->entrega->guia = Guia::factory()->create();
    $this->entrega->por_guia = true;
});

// Caminho feliz
test('job NotificarEntrega envia notificação ao usuário recebedor', function () {
    Notification::fake();

    NotificarEntrega::dispatchSync($this->entrega);

    Notification::assertTimesSent(1, ProcessoEntregue::class); // @phpstan-ignore-line
    Notification::assertSentTo($this->entrega->recebedor, ProcessoEntregue::class);
});

test('job NotificarEntrega cria a notificação com todos os parâmetros e canal esperados', function () {
    Notification::fake();

    NotificarEntrega::dispatchSync($this->entrega);

    Notification::assertSentTo(
        $this->entrega->recebedor,
        ProcessoEntregue::class,
        function (ProcessoEntregue $notification, $channels) {
            expect($notification->toArray(null))->toMatchArray([
                'guia_numero' => $this->entrega->guia->paraHumano,
                'processos' => $this->entrega->guia->processos->toArray(),
                'recebedor' => $this->entrega->guia->recebedor['nome'],
                'destino' => $this->entrega->guia->destino['nome'],
                'entregue_em' => $this->entrega->guia->gerada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'por_guia' => $this->entrega->por_guia,
                'url' => route('solicitacao.index'),
                'email_terceiros' => $this->entrega->guia->processos
                    ->pluck('solicitante.email')
                    ->unique()
                    ->toArray(),
            ])->and($channels)->toMatchArray(['mail']);

            return true;
        }
    );
});

test('job NotificarEntrega envia para a queue de prioridade baixa a execução da notificação', function () {
    Queue::fake()->except([
        NotificarEntrega::class,
    ]);

    NotificarEntrega::dispatchSync($this->entrega);

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        SendQueuedNotifications::class,
        fn (SendQueuedNotifications $job) => $job->notification::class === ProcessoEntregue::class
    );

    Queue::assertPushed(SendQueuedNotifications::class, 1);
});
