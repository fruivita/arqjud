<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\TipoProcesso;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar tipos de processo duplicados, isto é, com mesmo nome', function () {
    expect(
        fn () => TipoProcesso::factory(2)->create(['nome' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar tipo processo com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => TipoProcesso::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['nome',      Str::random(101), 'Data too long for column'], // máximo 100 caracteres
    ['nome',      null,             'cannot be null'],           // obrigatório
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

// Caminho feliz
test('aceita campos em seus tamanhos máximos', function () {
    TipoProcesso::factory()->create([
        'nome' => Str::random(100),
        'descricao' => Str::random(255),
    ]);

    expect(TipoProcesso::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    TipoProcesso::factory()->create(['descricao' => null]);

    expect(TipoProcesso::count())->toBe(1);
});

test('um tipo processo possui muitas caixas', function () {
    TipoProcesso::factory()->hasCaixas(3)->create();

    $tipo_processo = TipoProcesso::with('caixas')->first();

    expect($tipo_processo->caixas)->toHaveCount(3);
});

test('retorna os tipos de processo pelo escopo search que busca a partir do início do texto', function () {
    TipoProcesso::factory()->create(['nome' => 'foo']);
    TipoProcesso::factory()->create(['nome' => 'bar']);
    TipoProcesso::factory()->create(['nome' => 'baz']);
    TipoProcesso::factory()->create(['nome' => 'taz']);

    expect(TipoProcesso::search()->count())->toBe(4)
        ->and(TipoProcesso::search('ta')->count())->toBe(1)
        ->and(TipoProcesso::search('ba')->count())->toBe(2)
        ->and(TipoProcesso::search('oo')->count())->toBe(0);
});
