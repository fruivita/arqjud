<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Caixa;
use App\Models\Localidade;
use App\Pipes\Caixa\JoinLocalidadeCriadora;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('join da caixa com a localidade criadora', function () {
    $localidade = Localidade::factory()->hasCaixasCriadas(1)->create();

    $caixa = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidadeCriadora::class])
        ->thenReturn()
        ->pluck('criadoras.nome');

    expect($caixa->first())->toBe($localidade->nome);
});
