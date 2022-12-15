<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EnumsQueue;
use App\Jobs\NotificarOperadoresSolicitacao as JobNotificarOperadoresSolicitacao;
use App\Models\Processo;
use App\Models\Usuario;
use App\Pipes\Solicitacao\NotificarOperadoresSolicitacao;
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
});

// Caminho feliz
test('pipe NotificarOperadoresSolicitacao cria o job NotificarOperadoresSolicitacao para notificar os operadores', function () {
    Bus::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->solicitacao)
        ->through([NotificarOperadoresSolicitacao::class])
        ->thenReturn();

    Bus::assertNotDispatchedSync(JobNotificarOperadoresSolicitacao::class, 1);
});

test('pipe NotificarOperadoresSolicitacao envia o job para a querue de prioridade baixa', function () {
    Queue::fake();

    Pipeline::make()
        ->withTransaction()
        ->send($this->solicitacao)
        ->through([NotificarOperadoresSolicitacao::class])
        ->thenReturn();

    Queue::assertPushedOn(EnumsQueue::Media->value, JobNotificarOperadoresSolicitacao::class);
    Queue::assertPushed(JobNotificarOperadoresSolicitacao::class, 1);
});
