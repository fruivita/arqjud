<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\VolumeCaixa\JoinLocalidade;
use App\Models\Andar;
use App\Models\Caixa;
use App\Models\VolumeCaixa;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use Illuminate\Pipeline\Pipeline;

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

    $volume = app(Pipeline::class)
        ->send(VolumeCaixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($volume->first())->toBe($localidade->nome);
});

test('join da caixa com a localidade criadora', function () {
    $localidade = Localidade::factory()->has(Caixa::factory()->hasVolumes(1), 'caixasCriadas')->create();

    $volume = app(Pipeline::class)
        ->send(VolumeCaixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('criadoras.nome');

    expect($volume->first())->toBe($localidade->nome);
});
