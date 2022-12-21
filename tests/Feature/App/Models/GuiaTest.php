<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Guia;
use App\Pipes\Search;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Exceptions
test('lança exception ao tentar criar guias duplicadas, isto é, com mesmo ano e número', function () {
    expect(
        fn () => Guia::factory(2)->create([
            'numero' => 100,
            'ano' => 2020,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar guia com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Guia::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',    -1,               'Out of range'],             // min 0
    ['numero',    4294967296,       'Out of range'],             // max 4294967295
    ['numero',    'foo',            'Incorrect integer value'],  // não conversível em inteiro
    ['ano',       -1,               'Out of range value'],       // min 0
    ['ano',       65536,            'Out of range value'],       // max 65536
    ['ano',       'foo',            'Incorrect integer value'],  // não conversível em inteiro
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

// Caminho feliz
test('aceita campos em seus tamanhos máximos', function () {
    Guia::factory()->create([
        'numero' => 4294967295,
        'ano' => 65535,
        'descricao' => Str::random(255),
    ]);

    expect(Guia::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    Guia::factory()->create([
        'descricao' => null,
    ]);

    expect(Guia::count())->toBe(1);
});

test('uma guia refere-se a muitas solicitações de processo', function () {
    Guia::factory()->hasSolicitacoes(3)->create();

    $guia = Guia::with('solicitacoes')->first();

    expect($guia->solicitacoes)->toHaveCount(3);
});

test('gera o próximo número da guia (MAX + 1) de acordo com o ano informado', function () {
    Guia::factory()->create(['numero' => 100, 'ano' => 2020]);
    Guia::factory()->create(['numero' => 200, 'ano' => now()->year]);

    expect(Guia::proximoNumero(2020))->toBe(101)
        ->and(Guia::proximoNumero())->toBe(201)
        ->and(Guia::proximoNumero(2000))->toBe(1);
});

test('paraHumano retorna o número da guia formatado para exibição', function () {
    $guia = Guia::factory()->create();

    expect($guia->paraHumano)->toBe("{$guia->numero}/{$guia->ano}");
});

test('retorna as guias pelo escopo search que busca a partir do início do texto no número e no ano', function (string $termo, int $quantidade) {
    Guia::factory()->create(['numero' => 99999999, 'ano' => 55555]);
    Guia::factory()->create(['numero' => 77778888, 'ano' => 44444]);
    Guia::factory()->create(['numero' => 77777777, 'ano' => 33333]);
    Guia::factory()->create(['numero' => 66666666, 'ano' => 33332]);

    $query = Pipeline::make()
        ->send(Guia::query())
        ->through([Search::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 4],
    [99999, 1],
    [3333, 2],
    [777888, 0],
]);
