<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Predio;
use App\Models\Sala;
use App\Pipes\Sala\JoinLocalidade;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('join da sala até a localidade', function () {
    $localidade = Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()->hasSalas(1), 'andares'))
        ->create();

    $sala = Pipeline::make()
        ->send(Sala::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($sala->first())->toBe($localidade->nome);
});
