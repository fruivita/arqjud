<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Andar\JoinLocalidade;
use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Exceptions
test('lança exception ao tentar criar andares duplicados, isto é, com mesmo numero/apelido e prédio', function () {
    $predio = Predio::factory()->create();

    expect(
        fn () => Andar::factory(2)->create([
            'numero' => 100,
            'predio_id' => $predio->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Andar::factory(2)->create([
            'apelido' => 100,
            'predio_id' => $predio->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar andar com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Andar::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',    -2147483649,      'Out of range'],             // min -2147483648
    ['numero',    2147483648,       'Out of range'],             // max 2147483647
    ['numero',    'foo',            'Incorrect integer value'],  // não conversível em inteiro
    ['numero',    null,             'cannot be null'],           // obrigatório
    ['apelido',   Str::random(101), 'Data too long for column'], // máximo 100 caracteres
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Andar::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['predio_id', 99999999, 'Cannot add or update a child row'], // não existente
    ['predio_id', null,     'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('andares com apelido null não são consideradas duplicadas', function () {
    $predio = Predio::factory()->create();

    Andar::factory()->for($predio)->create(['apelido' => null]);
    Andar::factory()->for($predio)->create(['apelido' => null]);
    Andar::factory()->for($predio)->create(['apelido' => '10']);

    $predio->load(['andares' => function ($query) {
        $query->whereNull('apelido');
    }]);

    expect($predio->andares)->toHaveCount(2);
});

test('aceita campos em seus tamanhos mínimos', function () {
    Andar::factory()->create(['numero' => -2147483648]);

    expect(Andar::count())->toBe(1);
});

test('aceita campos em seus tamanhos máximos', function () {
    Andar::factory()->create([
        'numero' => 2147483647,
        'apelido' => Str::random(100),
        'descricao' => Str::random(255),
    ]);

    expect(Andar::count())->toBe(1);
});

test('zero é um valor válido para o número do andar', function () {
    Andar::factory()->create(['numero' => 0]);

    $andar = Andar::first();

    expect($andar->numero)->toBe(0);
});

test('campos opcionais estão definidos', function () {
    Andar::factory()->create([
        'apelido' => null,
        'descricao' => null,
    ]);

    expect(Andar::count())->toBe(1);
});

test('um andar pertence a um prédio', function () {
    $andar = Andar::factory()->for(Predio::factory(), 'predio')->create();

    $andar->load(['predio']);

    expect($andar->predio)->toBeInstanceOf(Predio::class);
});

test('um andar possui muitas salas', function () {
    Andar::factory()->hasSalas(3)->create();

    $andar = Andar::with('salas')->first();

    expect($andar->salas)->toHaveCount(3);
});

test('retorna os andares pelo escopo search que busca a partir do início do texto no número e apelido do andar', function (string $termo, int $quantidade) {
    Andar::factory()->create(['numero' => 99999988, 'apelido' => 'aaaaaaaa']);
    Andar::factory()->create(['numero' => 99999999, 'apelido' => 'ccccbbbb']);
    Andar::factory()->create(['numero' => 33333333, 'apelido' => 'cccccccc']);
    Andar::factory()->create(['numero' => 44444444, 'apelido' => 'dddddddd']);

    $query = Pipeline::make()
        ->send(Andar::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 4],
    ['ccc', 2],
    ['999999', 2],
    ['444444', 1],
    ['ccbb', 0],
]);

test('retorna os andares pelo escopo search que busca a partir do início do texto no nome do prédio pai', function (string $termo, int $quantidade) {
    Predio::factory()->hasAndares(2)->create(['nome' => 'aaaaaaaa']);
    Predio::factory()->hasAndares(3)->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Andar::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna os andares pelo escopo search que busca a partir do início do texto no nome da localidade pai', function (string $termo, int $quantidade) {
    Localidade::factory()
        ->has(Predio::factory()->hasAndares(2), 'predios')
        ->create(['nome' => 'aaaaaaaa']);
    Localidade::factory()
        ->has(Predio::factory()->hasAndares(3), 'predios')
        ->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Andar::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);
