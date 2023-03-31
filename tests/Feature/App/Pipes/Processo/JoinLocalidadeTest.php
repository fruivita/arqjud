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
use App\Models\Processo;
use App\Models\Sala;
use App\Models\TipoProcesso;
use App\Pipes\Processo\JoinLocalidade;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('join do processo até a localidade', function () {
    $localidade = Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()
                        ->has(Prateleira::factory()
                            ->has(Caixa::factory()->hasProcessos(1))))), 'andares'))
        ->create();

    $nome = Pipeline::make()
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('localidades.nome');

    expect($nome->first())->toBe($localidade->nome);
});

test('join da caixa com a localidade criadora', function () {
    $localidade = Localidade::factory()
        ->has(Caixa::factory()->hasProcessos(1), 'caixasCriadas')
        ->create();

    $nome = Pipeline::make()
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('criadoras.nome');

    expect($nome->first())->toBe($localidade->nome);
});

test('join da caixa com o tipo de processo', function () {

    $tipo_processo = TipoProcesso::factory()
        ->has(Caixa::factory()->hasProcessos(1), 'caixas')
        ->create();

    $nome = Pipeline::make()
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn()
        ->pluck('tipos_processo.nome');

    expect($nome->first())->toBe($tipo_processo->nome);
});
