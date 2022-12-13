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
use App\Models\VolumeCaixa;
use App\Pipes\VolumeCaixa\JoinLocalidade;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('join do volume da caixa até a localidade', function () {
    $localidade = Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()
                        ->has(Prateleira::factory()
                            ->has(Caixa::factory()->hasVolumes(1))))), 'andares'))
        ->create();

    $volume = Pipeline::make()
        ->send(VolumeCaixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($volume->first())->toBe($localidade->nome);
});

test('join da caixa com a localidade criadora', function () {
    $localidade = Localidade::factory()->has(Caixa::factory()->hasVolumes(1), 'caixasCriadas')->create();

    $volume = Pipeline::make()
        ->send(VolumeCaixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('criadoras.nome');

    expect($volume->first())->toBe($localidade->nome);
});
