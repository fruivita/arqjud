<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Lotacao;
use App\Pipes\Lotacao\JoinAll;
use Illuminate\Database\QueryException;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Exceptions
test('lança exception ao tentar criar solicitação com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Lotacao::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['administravel', 'foo', 'Incorrect integer value'], // não conversível em boolean
    ['administravel', null,  'cannot be null'],          // obrigatório
]);

// Caminho feliz
test('uma lotação pode ser destinatária de várias solicitações de processo', function () {
    $lotacao = Lotacao::factory()->hasSolicitacoes(3)->create();

    $lotacao->load('solicitacoes');

    expect($lotacao->solicitacoes)->toHaveCount(3);
});

test('retorna as lotações pelo escopo search que busca a partir do início do texto na sigla ou nome da lotação', function (string $termo, int $quantidade) {
    Lotacao::factory()->create(['sigla' => 'aaaabbbb', 'nome' => 'ggggffff']);
    Lotacao::factory()->create(['sigla' => 'ccccdddd', 'nome' => 'gggggggg']);
    Lotacao::factory()->create(['sigla' => 'cccccccc', 'nome' => 'aaaajjjj']);
    Lotacao::factory()->create(['sigla' => 'ggggdddd', 'nome' => 'hhhhhhhh']);

    $query = Pipeline::make()
        ->send(Lotacao::query())
        ->through([JoinAll::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 4],
    ['aaaa', 2],
    ['gggg', 3],
]);

test('retorna as lotações pelo escopo search que busca a partir do início do texto na sigla ou nome da lotação pai', function (string $termo, int $quantidade) {
    Lotacao::factory(2)->for(Lotacao::factory(['sigla' => 'aaaabbbb', 'nome' => 'eeeeffff']), 'lotacaoPai')->create();
    Lotacao::factory(3)->for(Lotacao::factory(['sigla' => 'ccccdddd', 'nome' => 'gggghhhh']), 'lotacaoPai')->create();

    $query = Pipeline::make()
        ->send(Lotacao::query())
        ->through([JoinAll::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 7],
    ['aaaa', 2 + 1],
    ['gggg', 3 + 1],
]);

test('método administraveis retorna todas as lotações administráveis', function () {
    Lotacao::factory(2)->create(['administravel' => false]);
    Lotacao::factory(5)->create(['administravel' => true]);

    expect(Lotacao::administraveis())->toHaveCount(5);
});
