<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Localidade;
use function Spatie\PestPluginTestTime\testTime;

// Caminho feliz
test('ordenação ascendente', function () {
    Localidade::factory()->create(['nome' => 'ccc']);
    Localidade::factory()->create(['nome' => 'aaa']);
    Localidade::factory()->create(['nome' => 'bbb']);

    $ordenados = Localidade::orderByOrLatest(['nome' => 'asc'])->get();

    $primeiro = $ordenados->get(0);
    $segundo = $ordenados->get(1);
    $terceiro = $ordenados->get(2);

    expect($ordenados)->toHaveCount(3)
        ->and($primeiro->nome)->toBe('aaa')
        ->and($segundo->nome)->toBe('bbb')
        ->and($terceiro->nome)->toBe('ccc');
});

test('ordenação descendente', function () {
    Localidade::factory()->create(['nome' => 'ccc']);
    Localidade::factory()->create(['nome' => 'bbb']);
    Localidade::factory()->create(['nome' => 'aaa']);

    $ordenados = Localidade::orderByOrLatest(['nome' => 'desc'])->get();

    $primeiro = $ordenados->get(0);
    $segundo = $ordenados->get(1);
    $terceiro = $ordenados->get(2);

    expect($ordenados)->toHaveCount(3)
        ->and($primeiro->nome)->toBe('ccc')
        ->and($segundo->nome)->toBe('bbb')
        ->and($terceiro->nome)->toBe('aaa');
});

test('se o array de ordenação não for informado, usa a ordenação padrão, isto é, ordena pela data de criação do registro mais recente para o mais antigo', function () {
    testTime()->freeze();
    Localidade::factory()->create(['nome' => 'primeiro']);

    testTime()->addMinute();
    Localidade::factory()->create(['nome' => 'segundo']);

    testTime()->addMinute();
    Localidade::factory()->create(['nome' => 'terceiro']);

    $ordenados = Localidade::orderByOrLatest([])->get();

    $primeiro = $ordenados->get(0);
    $segundo = $ordenados->get(1);
    $terceiro = $ordenados->get(2);

    expect($ordenados)->toHaveCount(3)
        ->and($primeiro->nome)->toBe('terceiro')
        ->and($segundo->nome)->toBe('segundo')
        ->and($terceiro->nome)->toBe('primeiro');
});

test('na ordenação default, se a data de criação dos registros for a mesma, ordena pelo ID desc', function () {
    testTime()->freeze();
    Localidade::factory()->create(['id' => 2, 'nome' => '2']);
    Localidade::factory()->create(['id' => 5, 'nome' => '5']);
    Localidade::factory()->create(['id' => 3, 'nome' => '3']);

    testTime()->addMinute();
    Localidade::factory()->create(['id' => 4, 'nome' => '4']);

    $ordenados = Localidade::orderByOrLatest([])->get();

    $primeiro = $ordenados->get(0);
    $segundo = $ordenados->get(1);
    $terceiro = $ordenados->get(2);
    $quarto = $ordenados->get(3);

    expect($ordenados)->toHaveCount(4)
        ->and($primeiro->nome)->toBe('4')
        ->and($segundo->nome)->toBe('5')
        ->and($terceiro->nome)->toBe('3')
        ->and($quarto->nome)->toBe('2');
});
