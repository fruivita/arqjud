<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Caixa\JoinLocalidadeCriadora;
use App\Models\Caixa;
use App\Models\Localidade;
use Illuminate\Pipeline\Pipeline;

// Caminho feliz
test('join da caixa com a localidade criadora', function () {
    $localidade = Localidade::factory()->hasCaixasCriadas(1)->create();

    $caixa = app(Pipeline::class)
        ->send(Caixa::query())
        ->through([JoinLocalidadeCriadora::class])
        ->thenReturn()
        ->pluck('criadoras.nome');

    expect($caixa->first())->toBe($localidade->nome);
});
