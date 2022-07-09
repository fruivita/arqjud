<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Predio;
use App\Models\Localidade;

use function Spatie\PestPluginTestTime\testTime;

// Caminho feliz
test('ordenação ascendente', function () {
    Predio::factory()->create(['nome' => 'foo']);
    Predio::factory()->create(['nome' => 'bar']);
    Predio::factory()->create(['nome' => 'baz']);

    $ordenados = Predio::orderByWhen(['nome' => 'asc'])->get();

    $primeiro = $ordenados->get(0);
    $segundo = $ordenados->get(1);
    $terceiro = $ordenados->get(2);

    expect($ordenados)->toHaveCount(3)
    ->and($primeiro->nome)->toBe('bar')
    ->and($segundo->nome)->toBe('baz')
    ->and($terceiro->nome)->toBe('foo');
});

test('ordenação descendente', function () {
    Predio::factory()->create(['nome' => 'foo']);
    Predio::factory()->create(['nome' => 'baz']);
    Predio::factory()->create(['nome' => 'bar']);

    $ordenados = Predio::orderByWhen(['nome' => 'desc'])->get();

    $primeiro = $ordenados->get(0);
    $segundo = $ordenados->get(1);
    $terceiro = $ordenados->get(2);

    expect($ordenados)->toHaveCount(3)
    ->and($primeiro->nome)->toBe('foo')
    ->and($segundo->nome)->toBe('baz')
    ->and($terceiro->nome)->toBe('bar');
});

test('se o array de ordenação não for informado, usa a ordenação padrão, isto é, ordena pela data de criação do registro mais recente para o mais antigo', function () {
    testTime()->freeze();
    Predio::factory()->create(['nome' => 'foo']);

    testTime()->addMinute();
    Predio::factory()->create(['nome' => 'bar']);

    testTime()->addMinute();
    Predio::factory()->create(['nome' => 'baz']);

    $ordenados = Predio::orderByWhen([])->get();

    $primeiro = $ordenados->get(0);
    $segundo = $ordenados->get(1);
    $terceiro = $ordenados->get(2);

    expect($ordenados)->toHaveCount(3)
    ->and($primeiro->nome)->toBe('baz')
    ->and($segundo->nome)->toBe('bar')
    ->and($terceiro->nome)->toBe('foo');
});

test('na ordenação default, se a data de criação dos registros for a mesma, ordena pelo ID desc', function () {
    testTime()->freeze();
    Predio::factory()->create(['id' => 2 , 'nome' => 'bar']);
    Predio::factory()->create(['id' => 3, 'nome' => 'foo']);

    testTime()->addMinute();
    Predio::factory()->create(['id' => 5, 'nome' => 'baz']);

    $ordenados = Predio::orderByWhen([])->get();

    $primeiro = $ordenados->get(0);
    $segundo = $ordenados->get(1);
    $terceiro = $ordenados->get(2);

    expect($ordenados)->toHaveCount(3)
    ->and($primeiro->nome)->toBe('baz')
    ->and($segundo->nome)->toBe('foo')
    ->and($terceiro->nome)->toBe('bar');
});

test('se os modelos forem obtidos pela query hierárquica, é possível ordenar por múltiplas colunas', function () {
    $localidade_foo = Localidade::factory()->create(['nome' => 'foo']);
    $localidade_bar = Localidade::factory()->create(['nome' => 'bar']);

    Predio::factory()->for($localidade_foo, 'localidade')->create(['nome' => 'loren']);
    Predio::factory()->for($localidade_foo, 'localidade')->create(['nome' => 'ipsun']);
    Predio::factory()->for($localidade_foo, 'localidade')->create(['nome' => 'dolor']);
    Predio::factory()->for($localidade_bar, 'localidade')->create(['nome' => 'tempor']);
    Predio::factory()->for($localidade_bar, 'localidade')->create(['nome' => 'labore']);

    $ordenados = Predio::hierarquia()->orderByWhen(['localidades.nome' => 'asc', 'predios.nome' => 'asc'])->get();

    $primeiro = $ordenados->get(0);
    $segundo = $ordenados->get(1);
    $terceiro = $ordenados->get(2);
    $quarto = $ordenados->get(3);
    $quinto = $ordenados->get(4);

    expect($ordenados)->toHaveCount(5)
    ->and($primeiro->nome)->toBe('labore')
    ->and($segundo->nome)->toBe('tempor')
    ->and($terceiro->nome)->toBe('dolor')
    ->and($quarto->nome)->toBe('ipsun')
    ->and($quinto->nome)->toBe('loren');
});
