<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Predio\JoinLocalidade;
use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Pipeline\Pipeline;

// Caminho feliz
test('join do prédio até a localidade', function () {
    $localidade = Localidade::factory()->hasPredios(1)->create();

    $predio = app(Pipeline::class)
        ->send(Predio::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($predio->first())->toBe($localidade->nome);
});
