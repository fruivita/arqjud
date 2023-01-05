<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Lotacao;
use App\Pipes\Lotacao\JoinAll;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('join da tabela lotacoes_pai', function () {
    $lotacao_pai = Lotacao::factory()->create();
    $lotacao = Lotacao::factory()->for($lotacao_pai, 'lotacaoPai')->create();

    $lotacao = Pipeline::make()
        ->send(Lotacao::query())
        ->through([JoinAll::class])
        ->thenReturn()
        ->pluck('lotacoes_pai.nome');

    expect($lotacao->first())->toBe($lotacao_pai->nome);
});
