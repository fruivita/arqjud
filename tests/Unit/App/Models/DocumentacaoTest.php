<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Documentacao;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar documentação duplicada, isto é, com links de aplicação iguais', function () {
    expect(
        fn () => Documentacao::factory(2)->create(['app_link' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criardocumentação da aplicação com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Documentacao::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['app_link', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
    ['app_link', null,             'cannot be null'],           // obrigatório
    ['doc_link', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

// Caminho feliz
test('cria muitos documentações da aplicação', function () {
    Documentacao::factory(30)->create();

    expect(Documentacao::count())->toBe(30);
});

test('aceita campos em seus tamanhos máximos', function () {
    Documentacao::factory()->create([
        'app_link' => Str::random(255),
        'doc_link' => Str::random(255),
    ]);

    expect(Documentacao::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    Documentacao::factory()->create(['doc_link' => null]);

    expect(Documentacao::count())->toBe(1);
});

test('retorna a documentação ordenada pelo escopo de ordenação padrão', function () {
    Documentacao::factory()->create(['app_link' => 'foo']);
    Documentacao::factory()->create(['app_link' => 'bar']);
    Documentacao::factory()->create(['app_link' => 'baz']);

    $documentacao = Documentacao::ordenacaoPadrao()->get();

    expect($documentacao->get(0)->app_link)->toBe('bar')
    ->and($documentacao->get(1)->app_link)->toBe('baz')
    ->and($documentacao->get(2)->app_link)->toBe('foo');
});
