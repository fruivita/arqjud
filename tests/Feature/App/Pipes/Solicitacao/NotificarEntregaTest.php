<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EnumsQueue;
use App\Jobs\NotificarEntrega as JobNotificarEntrega;
use App\Models\Guia;
use App\Models\Usuario;
use App\Pipes\Solicitacao\NotificarEntrega;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use MichaelRubel\EnhancedPipeline\Pipeline;

beforeEach(function () {
    $this->entrega = new stdClass();
    $this->entrega->recebedor = Usuario::factory()->create();
    $this->entrega->guia = Guia::factory()->create();
    $this->entrega->por_guia = true;
});

// Caminho feliz
test('pipe NotificarEntrega cria o job NotificarEntrega para notificar os envolvidos na entrega dos processos solicitados', function () {
    Bus::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->entrega)
        ->through([NotificarEntrega::class])
        ->thenReturn();

    Bus::assertNotDispatchedSync(JobNotificarEntrega::class, 1); // @phpstan-ignore-line
});

test('pipe NotificarEntrega envia o job para a queue de prioridade baixa', function () {
    Queue::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->entrega)
        ->through([NotificarEntrega::class])
        ->thenReturn();

    Queue::assertPushedOn(EnumsQueue::Media->value, JobNotificarEntrega::class);
    Queue::assertPushed(JobNotificarEntrega::class, 1);
});
