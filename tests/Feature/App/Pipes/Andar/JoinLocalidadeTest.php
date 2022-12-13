<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Predio;
use App\Pipes\Andar\JoinLocalidade;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('join do andar até a localidade', function () {
    $localidade = Localidade::factory()->has(Predio::factory()->hasAndares(1))->create();

    $andar = Pipeline::make()
        ->send(Andar::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($andar->first())->toBe($localidade->nome);
});
