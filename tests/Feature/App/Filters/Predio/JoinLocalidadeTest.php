<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Predio\JoinLocalidade;
use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Pipeline\Pipeline;

// Caminho feliz
test('join da localidade com o prédio', function () {
    Predio::factory()->for(Localidade::factory(['nome' => 'foo']), 'localidade')->create(['id' => 1]);

    $localidade = app(Pipeline::class)
        ->send(Predio::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($localidade->first())->toBe('foo');
});
