<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Caixa;
use App\Models\Localidade;
use App\Pipes\Caixa\Atualizar;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('atualiza a caixa baseado nos dados do request', function () {
    $localidade = Localidade::factory()->create();

    $dados = [
        'numero' => 500,
        'ano' => 2000,
        'guarda_permanente' => true,
        'complemento' => 'foo',
        'descricao' => 'foo bar',
        'localidade_criadora_id' => $localidade->id,
    ];

    request()->merge($dados);

    Pipeline::make()
        ->send(Caixa::factory()->create())
        ->through([Atualizar::class])
        ->thenReturn();

    expect(Caixa::first()->only(array_keys($dados)))
        ->toBe($dados);
});
