<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use App\Pipes\Caixa\JoinLocalidade;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('join da caixa até a localidade', function () {
    $localidade = Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()
                        ->has(Prateleira::factory()->hasCaixas(1)))), 'andares'))
        ->create();

    $caixa = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($caixa->first())->toBe($localidade->nome);
});

test('join da caixa com a localidade criadora', function () {
    $localidade = Localidade::factory()->hasCaixasCriadas(1)->create();

    $caixa = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('criadoras.nome');

    expect($caixa->first())->toBe($localidade->nome);
});
