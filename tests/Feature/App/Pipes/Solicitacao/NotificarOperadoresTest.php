<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Events\ProcessoSolicitadoPeloUsuario;
use App\Models\Processo;
use App\Models\Usuario;
use App\Pipes\Solicitacao\NotificarOperadores;
use Illuminate\Support\Facades\Event;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('pipe NotificarOperadores dispara evento ProcessoSolicitadoPeloUsuario para notificar operadores', function () {
    Event::fake();

    $processos = Processo::factory(3)
        ->create()
        ->pluck('numero')
        ->transform('apenasNumeros')
        ->toArray();

    $solicitacao = new stdClass();
    $solicitacao->processos = $processos;
    $solicitacao->solicitante = Usuario::factory()->create();

    Pipeline::make()
        ->withTransaction()
        ->send($solicitacao)
        ->through([NotificarOperadores::class])
        ->thenReturn();

    Event::assertDispatched(ProcessoSolicitadoPeloUsuario::class, function (ProcessoSolicitadoPeloUsuario $event) use ($processos, $solicitacao) {
        expect($event->processos)->toBe($processos)
            ->and($event->solicitante->is($solicitacao->solicitante))->toBeTrue()
            ->and($event->solicitada_em->toString())->toBe(now()->toString());

        return true;
    });
});
