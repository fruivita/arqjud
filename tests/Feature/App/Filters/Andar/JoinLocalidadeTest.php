<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Andar\JoinLocalidade;
use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Pipeline\Pipeline;

// Caminho feliz
test('join do andar até a localidade', function () {
    $localidade = Localidade::factory()->has(Predio::factory()->hasAndares(1))->create();

    $andar = app(Pipeline::class)
        ->send(Andar::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($andar->first())->toBe($localidade->nome);
});
