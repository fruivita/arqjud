<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Caixa\JoinLocalidade;
use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use Illuminate\Pipeline\Pipeline;

// Caminho feliz
test('join da caixa até a localidade', function () {
    $localidade = Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()
                        ->has(Prateleira::factory()->hasCaixas(1)))), 'andares'))
        ->create();

    $caixa = app(Pipeline::class)
        ->send(Caixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($caixa->first())->toBe($localidade->nome);
});

test('join da caixa com a localidade criadora', function () {
    $localidade = Localidade::factory()->hasCaixasCriadas(1)->create();

    $caixa = app(Pipeline::class)
        ->send(Caixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('criadoras.nome');

    expect($caixa->first())->toBe($localidade->nome);
});
