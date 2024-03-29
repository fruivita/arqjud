<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EnumsQueue;
use App\Jobs\NotificarOperadoresSolicitacao;
use App\Models\Lotacao;
use App\Models\Processo;
use App\Models\Usuario;
use App\Pipes\Solicitacao\NotificarOperadores;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use MichaelRubel\EnhancedPipeline\Pipeline;

beforeEach(function () {
    $processos = Processo::factory(3)
        ->create()
        ->pluck('numero')
        ->transform('apenasNumeros')
        ->toArray();

    $this->solicitacao = new stdClass();
    $this->solicitacao->processos = $processos;
    $this->solicitacao->solicitante = Usuario::factory()->create();
    $this->solicitacao->destino = Lotacao::factory()->create();
});

// Caminho feliz
test('pipe NotificarOperadores cria o job NotificarOperadoresSolicitacao para notificar os operadores', function () {
    Bus::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->solicitacao)
        ->through([NotificarOperadores::class])
        ->thenReturn();

    Bus::assertNotDispatchedSync(NotificarOperadoresSolicitacao::class, 1); // @phpstan-ignore-line
});

test('pipe NotificarOperadores envia o job para a queue de prioridade baixa', function () {
    Queue::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->solicitacao)
        ->through([NotificarOperadores::class])
        ->thenReturn();

    Queue::assertPushedOn(EnumsQueue::Media->value, NotificarOperadoresSolicitacao::class);
    Queue::assertPushed(NotificarOperadoresSolicitacao::class, 1);
});
