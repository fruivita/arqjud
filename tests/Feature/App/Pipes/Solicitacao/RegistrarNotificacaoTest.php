<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Solicitacao;
use App\Pipes\Solicitacao\RegistrarNotificacao;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\PestPluginTestTime\testTime;

// Caminho feliz
test('pipe RegistrarNotificacao registra a data de notificação do usuário', function () {
    testTime()->freeze();

    $solicitacao = Solicitacao::factory()->solicitada()->create();
    $processo = $solicitacao->processo()->first();

    $notificar = new \stdClass();
    $notificar->processo = apenasNumeros($processo->numero);

    expect($solicitacao->notificado_em)->toBeNull();

    $pipe = Pipeline::make()
        ->withTransaction()
        ->send($notificar)
        ->through([RegistrarNotificacao::class])
        ->thenReturn();

    $solicitacao->refresh();

    expect($solicitacao->notificado_em->toString())->toBe(now()->toString())
        ->and($pipe->solicitante->id)->toBe($solicitacao->solicitante->id);
});
