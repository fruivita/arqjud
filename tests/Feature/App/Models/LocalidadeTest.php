<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar localidades duplicadas, isto é, com mesmo nome', function () {
    expect(
        fn () => Localidade::factory(2)->create(['nome' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar localidade com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Localidade::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['nome',      Str::random(101), 'Data too long for column'], // máximo 100 caracteres
    ['nome',      null,             'cannot be null'],           // obrigatório
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

// Caminho feliz
test('aceita campos em seus tamanhos máximos', function () {
    Localidade::factory()->create([
        'nome' => Str::random(100),
        'descricao' => Str::random(255),
    ]);

    expect(Localidade::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    Localidade::factory()->create(['descricao' => null]);

    expect(Localidade::count())->toBe(1);
});

test('uma localidade possui muitos prédios', function () {
    Localidade::factory()->has(Predio::factory(3), 'predios')->create();

    $localidade = Localidade::with('predios')->first();

    expect($localidade->predios)->toHaveCount(3);
});

test('retorna as localidades pelo escopo search que busca a partir do início do texto', function () {
    Localidade::factory()->create(['nome' => 'foo']);
    Localidade::factory()->create(['nome' => 'bar']);
    Localidade::factory()->create(['nome' => 'baz']);
    Localidade::factory()->create(['nome' => 'taz']);

    expect(Localidade::search()->count())->toBe(4)
        ->and(Localidade::search('ta')->count())->toBe(1)
        ->and(Localidade::search('ba')->count())->toBe(2)
        ->and(Localidade::search('oo')->count())->toBe(0);
});
