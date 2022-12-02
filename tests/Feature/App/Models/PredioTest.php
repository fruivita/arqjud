<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar prédios duplicados, isto é, com mesmo nome e localidade', function () {
    $localidade = Localidade::factory()->create();

    expect(
        fn () => Predio::factory(2)->create([
            'nome' => 'foo',
            'localidade_id' => $localidade->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar prédio com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Predio::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['nome',      Str::random(101), 'Data too long for column'], // máximo 100 caracteres
    ['nome',      null,             'cannot be null'],           // obrigatório
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Predio::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['localidade_id', 99999999, 'Cannot add or update a child row'], // não existente
    ['localidade_id', null,     'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('aceita campos em seus tamanhos máximos', function () {
    Predio::factory()->create([
        'nome' => Str::random(100),
        'descricao' => Str::random(255),
    ]);

    expect(Predio::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    Predio::factory()->create(['descricao' => null]);

    expect(Predio::count())->toBe(1);
});

test('um prédio pertence a uma localidade', function () {
    $predio = Predio::factory()->for(Localidade::factory(), 'localidade')->create();

    $predio->load(['localidade']);

    expect($predio->localidade)->toBeInstanceOf(Localidade::class);
});

test('retorna os prédios pelo escopo search que busca a partir do início do texto', function () {
    $localidade_1 = Localidade::factory()->create(['nome' => 'foo']);
    Localidade::factory()->create(['nome' => 'bar']);
    Localidade::factory()->create(['nome' => 'baz']);
    $localidade_2 = Localidade::factory()->create(['nome' => 'taz']);

    Predio::factory()->for($localidade_1, 'localidade')->create(['nome' => 'bar']);
    Predio::factory()->for($localidade_1, 'localidade')->create(['nome' => 'baz']);
    Predio::factory()->for($localidade_2, 'localidade')->create(['nome' => 'taz']);

    expect(Predio::search()->count())->toBe(3)
        ->and(Predio::search('fo')->count())->toBe(2)
        ->and(Predio::search('ba')->count())->toBe(2)
        ->and(Predio::search('az')->count())->toBe(0);
});
