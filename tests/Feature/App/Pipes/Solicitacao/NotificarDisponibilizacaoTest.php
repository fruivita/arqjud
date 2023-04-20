<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EnumsQueue;
use App\Jobs\NotificarSolicitanteProcessoDisponivel;
use App\Models\Processo;
use App\Models\Usuario;
use App\Pipes\Solicitacao\NotificarDisponibilizacao;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use MichaelRubel\EnhancedPipeline\Pipeline;

beforeEach(function () {
    $processo = Processo::factory()
        ->create()
        ->pluck('numero')
        ->transform('apenasNumeros')
        ->first();

    $this->notificar = new \stdClass();
    $this->notificar->processo = $processo;
    $this->notificar->solicitante = Usuario::factory()->create();
});

// Caminho feliz
test('pipe NotificarDisponibilizacao cria o job NotificarSolicitanteProcessoDisponivel para notificar o solicitante', function () {
    Bus::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->notificar)
        ->through([NotificarDisponibilizacao::class])
        ->thenReturn();

    Bus::assertNotDispatchedSync(NotificarSolicitanteProcessoDisponivel::class, 1); // @phpstan-ignore-line
});

test('pipe NotificarDisponibilizacao envia o job para a queue de prioridade baixa', function () {
    Queue::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->notificar)
        ->through([NotificarDisponibilizacao::class])
        ->thenReturn();

    Queue::assertPushedOn(EnumsQueue::Media->value, NotificarSolicitanteProcessoDisponivel::class);
    Queue::assertPushed(NotificarSolicitanteProcessoDisponivel::class, 1);
});
