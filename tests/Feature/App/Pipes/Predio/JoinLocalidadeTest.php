<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Localidade;
use App\Models\Predio;
use App\Pipes\Predio\JoinLocalidade;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('join do prédio até a localidade', function () {
    $localidade = Localidade::factory()->hasPredios(1)->create();

    $predio = Pipeline::make()
        ->send(Predio::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($predio->first())->toBe($localidade->nome);
});
