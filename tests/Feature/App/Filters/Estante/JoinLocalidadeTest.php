<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Estante\JoinLocalidade;
use App\Models\Andar;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Predio;
use App\Models\Sala;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('join da estante até a localidade', function () {
    $localidade = Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()->hasEstantes(1)), 'andares'))
        ->create();

    $estante = Pipeline::make()
        ->send(Estante::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($estante->first())->toBe($localidade->nome);
});
