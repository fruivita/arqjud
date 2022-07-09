<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Predio;
use App\Models\Localidade;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar localidades duplicadas, isto é, com mesmo nome', function () {
    expect(
        fn () => Localidade::factory(2)->create(['nome' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar localidade com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Localidade::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['nome',      Str::random(101), 'Data too long for column'], // máximo 100 caracteres
    ['nome',      null,             'cannot be null'],           // obrigatório
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

// Caminho feliz
test('cria muitas localidades', function () {
    Localidade::factory(30)->create();

    expect(Localidade::count())->toBe(30);
});

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

test('método linksPais retorna as rotas de edição ordenadas do pai mais distante para o mais próximo se root for false', function () {
    $localidade = Localidade::factory()->create();

    expect($localidade->linksPais(false))->toBeEmpty();
});

test('método linksPais retorna as rotas de edição, inclusise do elemento root, ordenadas do pai mais distante para o mais próximo se root for true', function () {
    $localidade = Localidade::factory()->create();

    expect($localidade->linksPais(true)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $localidade->id),
    ]);
});

test('método hierarquia retorna todas as localidades com seu respectivo número de prédios', function () {
    Localidade::factory()->create(['nome' => 'foo']);
    Localidade::factory()->has(Predio::factory(1), 'predios')->create(['nome' => 'bar']);
    Localidade::factory()->has(Predio::factory(2), 'predios')->create(['nome' => 'baz']);

    $localidades = Localidade::hierarquia()->get();

    $localidade_foo = $localidades->firstWhere('nome', 'foo');
    $localidade_bar = $localidades->firstWhere('nome', 'bar');
    $localidade_baz = $localidades->firstWhere('nome', 'baz');

    expect($localidades)->toHaveCount(3)
    ->and($localidade_foo->predios_count)->toBe(0)
    ->and($localidade_bar->predios_count)->toBe(1)
    ->and($localidade_baz->predios_count)->toBe(2);
});
