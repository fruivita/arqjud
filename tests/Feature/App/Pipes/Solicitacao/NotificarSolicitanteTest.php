<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EnumsQueue;
use App\Jobs\NotificarSolicitanteSolicitacao;
use App\Models\Lotacao;
use App\Models\Processo;
use App\Models\Usuario;
use App\Pipes\Solicitacao\NotificarSolicitante;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use MichaelRubel\EnhancedPipeline\Pipeline;

beforeEach(function () {
    $processos = Processo::factory(3)
        ->create()
        ->pluck('numero')
        ->transform('apenasNumeros')
        ->toArray();

    $this->solicitacao = new \stdClass();
    $this->solicitacao->processos = $processos;
    $this->solicitacao->solicitante = Usuario::factory()->create();
    $this->solicitacao->destino = Lotacao::factory()->create();
    $this->solicitacao->solicitada_em = now();
});

// Caminho feliz
test('pipe NotificarSolicitante cria o job NotificarSolicitanteSolicitacao para notificar o solicitante', function () {
    Bus::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->solicitacao)
        ->through([NotificarSolicitante::class])
        ->thenReturn();

    Bus::assertNotDispatchedSync(NotificarSolicitanteSolicitacao::class, 1); // @phpstan-ignore-line
});

test('pipe NotificarSolicitante envia o job para a querue de prioridade baixa', function () {
    Queue::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->solicitacao)
        ->through([NotificarSolicitante::class])
        ->thenReturn();

    Queue::assertPushedOn(EnumsQueue::Media->value, NotificarSolicitanteSolicitacao::class);
    Queue::assertPushed(NotificarSolicitanteSolicitacao::class, 1);
});
