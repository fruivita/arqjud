<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Localidade;

// Caminho feliz
test('filtra os registros que cotenham o termo pesquisável', function () {
    Localidade::factory()->create(['nome' => 'foo']);
    Localidade::factory()->create(['nome' => 'bar']);
    Localidade::factory()->create(['nome' => 'baz']);

    $resultado = Localidade::orWhereLike('nome', 'a')->orderBy('nome', 'asc')->get();

    $localidade_bar = $resultado->get(0);
    $localidade_baz = $resultado->get(1);

    expect($resultado)->toHaveCount(2)
    ->and($localidade_bar->nome)->toBe('bar')
    ->and($localidade_baz->nome)->toBe('baz');
});

test('filtra os registros utilizando múltiplas colunas que contenham o termo pesquisável', function () {
    Caixa::factory()->create(['numero' => 150, 'ano' => 1999]);
    Caixa::factory()->create(['numero' => 500, 'ano' => 2021]);
    Caixa::factory()->create(['numero' => 329, 'ano' => 2021]);

    $resultado = Caixa::orWhereLike(['numero', 'ano'], 9)->orderBy('numero', 'asc')->get();

    $caixa_150 = $resultado->get(0);
    $caixa_329 = $resultado->get(1);

    expect($resultado)->toHaveCount(2)
    ->and($caixa_150->numero)->toBe(150)
    ->and($caixa_150->ano)->toBe(1999)
    ->and($caixa_329->numero)->toBe(329)
    ->and($caixa_329->ano)->toBe(2021);
});

test('zero é um termo válido para pesquisa', function () {
    Andar::factory()->create(['numero' => 0]);
    Andar::factory()->create(['numero' => 11]);

    $resultado = Andar::orWhereLike('numero', '0')->get();

    $andar = $resultado->first();

    expect($resultado)->toHaveCount(1)
    ->and($andar->numero)->toBe(0);
});

test('se o termo pesquisável não for informado, a clásula where não será aplicada', function () {
    $query = Localidade::orWhereLike('nome', '')->toSql();

    expect($query)->toBe('select * from `localidades`');
});
