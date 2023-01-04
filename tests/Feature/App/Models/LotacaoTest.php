<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Lotacao;
use Illuminate\Database\QueryException;

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
