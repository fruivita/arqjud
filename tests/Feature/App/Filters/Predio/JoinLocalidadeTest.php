<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Predio\JoinLocalidade;
use App\Models\Localidade;
use App\Models\Predio;
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
