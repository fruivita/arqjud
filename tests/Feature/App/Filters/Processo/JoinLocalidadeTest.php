<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Processo\JoinLocalidade;
use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Processo;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use App\Models\VolumeCaixa;
use Illuminate\Pipeline\Pipeline;

// Caminho feliz
test('join do processo até a localidade', function () {
    $localidade = Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()
                        ->has(Prateleira::factory()
                            ->has(Caixa::factory()
                                ->has(VolumeCaixa::factory()->hasProcessos(1), 'volumes'))))), 'andares'))
        ->create();

    $volume = app(Pipeline::class)
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($volume->first())->toBe($localidade->nome);
});

test('join da caixa com a localidade criadora', function () {
    $localidade = Localidade::factory()
        ->has(Caixa::factory()
            ->has(VolumeCaixa::factory()->hasProcessos(1), 'volumes'), 'caixasCriadas')
        ->create();

    $volume = app(Pipeline::class)
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('criadoras.nome');

    expect($volume->first())->toBe($localidade->nome);
});
