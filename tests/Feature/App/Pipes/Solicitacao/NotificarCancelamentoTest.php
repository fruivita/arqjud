<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EnumsQueue;
use App\Jobs\NotificarSolicitanteCancelamento;
use App\Models\Lotacao;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Pipes\Solicitacao\NotificarCancelamento;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use MichaelRubel\EnhancedPipeline\Pipeline;

beforeEach(function () {
    $this->solicitacao = new stdClass();
    $this->solicitacao->processo = '11111111111111111111';
    $this->solicitacao->solicitante = Usuario::factory()->create();
    $this->solicitacao->destino = Lotacao::factory()->create();
    $this->solicitacao->solicitada_em = now();
    $this->solicitacao->operador = Usuario::factory()->create();
    $this->solicitacao->cancelada_em = now();
});

// Caminho feliz
test('pipe NotificarCancelamento cria o job NotificarSolicitanteCancelamento para notificar os operadores', function () {
    Bus::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->solicitacao)
        ->through([NotificarCancelamento::class])
        ->thenReturn();

    Bus::assertNotDispatchedSync(NotificarSolicitanteCancelamento::class, 1); // @phpstan-ignore-line
});

test('pipe NotificarCancelamento envia o job para a querue de prioridade baixa', function () {
    Queue::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->solicitacao)
        ->through([NotificarCancelamento::class])
        ->thenReturn();

    Queue::assertPushedOn(EnumsQueue::Media->value, NotificarSolicitanteCancelamento::class);
    Queue::assertPushed(NotificarSolicitanteCancelamento::class, 1);
});
