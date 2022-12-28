<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EnumsQueue;
use App\Jobs\NotificarSolicitanteDevolucao;
use App\Models\Usuario;
use App\Pipes\Solicitacao\NotificarDevolucao;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use MichaelRubel\EnhancedPipeline\Pipeline;

beforeEach(function () {
    $this->devolucao = new stdClass();
    $this->devolucao->processo = '1111111111';
    $this->devolucao->devolvido_em = now();
    $this->devolucao->solicitante = Usuario::factory()->create();
});

// Caminho feliz
test('pipe NotificarDevolucao cria o job NotificarSolicitanteDevolucao para notificar o solicitante da devolução de seu processo ao arquivo', function () {
    Bus::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->devolucao)
        ->through([NotificarDevolucao::class])
        ->thenReturn();

    Bus::assertNotDispatchedSync(NotificarSolicitanteDevolucao::class, 1); // @phpstan-ignore-line
});

test('pipe NotificarDevolucao envia o job para a querue de prioridade baixa', function () {
    Queue::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->devolucao)
        ->through([NotificarDevolucao::class])
        ->thenReturn();

    Queue::assertPushedOn(EnumsQueue::Media->value, NotificarSolicitanteDevolucao::class);
    Queue::assertPushed(NotificarSolicitanteDevolucao::class, 1);
});
