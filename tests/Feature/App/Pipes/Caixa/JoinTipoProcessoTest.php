<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Caixa;
use App\Models\TipoProcesso;
use App\Pipes\Caixa\JoinTipoProcesso;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('join da caixa com o tipo de processo', function () {
    $tipo_processo = TipoProcesso::factory()->hasCaixas(1)->create();

    $caixa = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinTipoProcesso::class])
        ->thenReturn()
        ->pluck('tipos_processo.nome');

    expect($caixa->first())->toBe($tipo_processo->nome);
});
