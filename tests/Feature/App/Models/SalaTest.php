<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Andar;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Predio;
use App\Models\Sala;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar salas duplicadas, isto é, com mesmo número e andar', function () {
    $andar = Andar::factory()->create();

    expect(
        fn () => Sala::factory(2)->create([
            'numero' => '100',
            'andar_id' => $andar->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar sala com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Sala::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',    null,             'cannot be null'],           // obrigatório
    ['numero',    Str::random(51),  'Data too long for column'], // máximo 50 caracteres
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Sala::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['andar_id', 99999999, 'Cannot add or update a child row'], // não existente
    ['andar_id', null,     'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('aceita campos em seus tamanhos máximos', function () {
    Sala::factory()->create([
        'numero' => Str::random(50),
        'descricao' => Str::random(255),
    ]);

    expect(Sala::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    Sala::factory()->create(['descricao' => null]);

    expect(Sala::count())->toBe(1);
});

test('uma sala pertence a um andar', function () {
    $sala = Sala::factory()->for(Andar::factory(), 'andar')->create();

    $sala->load(['andar']);

    expect($sala->andar)->toBeInstanceOf(Andar::class);
});

test('uma sala possui muitas estantes', function () {
    Sala::factory()->has(Estante::factory(3), 'estantes')->create();

    $sala = Sala::with('estantes')->first();

    expect($sala->estantes)->toHaveCount(3);
});

test('retorna as salas pelo escopo search que busca a partir do início do texto no número do sala', function (string $termo, int $quantidade) {
    Sala::factory()->create(['numero' => 'aaaaaaaa']);
    Sala::factory()->create(['numero' => 'ccccbbbb']);
    Sala::factory()->create(['numero' => 'cccccccc']);
    Sala::factory()->create(['numero' => 'dddddddd']);

    $query = Sala::query()
        ->join('andares', 'andares.id', 'salas.andar_id')
        ->join('predios', 'predios.id', 'andares.predio_id')
        ->join('localidades', 'localidades.id', 'predios.localidade_id');

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 4],
    ['ccc', 2],
    ['ddd', 1],
    ['ccbb', 0],
]);

test('retorna as salas pelo escopo search que busca a partir do início do texto no número e apelido do andar', function (string $termo, int $quantidade) {
    Andar::factory()->has(Sala::factory(2), 'salas')->create(['numero' => 10, 'apelido' => 'aaaaaaaa']);
    Andar::factory()->has(Sala::factory(3), 'salas')->create(['numero' => 20, 'apelido' => 'bbbbbbbb']);

    $query = Sala::query()
        ->join('andares', 'andares.id', 'salas.andar_id')
        ->join('predios', 'predios.id', 'andares.predio_id')
        ->join('localidades', 'localidades.id', 'predios.localidade_id');

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
    [10, 2],
    [20, 3],
]);

test('retorna as salas pelo escopo search que busca a partir do início do texto no nome do prédio', function (string $termo, int $quantidade) {
    Predio::factory()->has(Andar::factory()->hasSalas(2), 'andares')->create(['nome' => 'aaaaaaaa']);
    Predio::factory()->has(Andar::factory()->hasSalas(3), 'andares')->create(['nome' => 'bbbbbbbb']);

    $query = Sala::query()
        ->join('andares', 'andares.id', 'salas.andar_id')
        ->join('predios', 'predios.id', 'andares.predio_id')
        ->join('localidades', 'localidades.id', 'predios.localidade_id');

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna as salas pelo escopo search que busca a partir do início do texto no nome da localidade', function (string $termo, int $quantidade) {
    Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()->hasSalas(2), 'andares'))
        ->create(['nome' => 'aaaaaaaa']);

    Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()->hasSalas(3), 'andares'))
        ->create(['nome' => 'bbbbbbbb']);

    $query = Sala::query()
        ->join('andares', 'andares.id', 'salas.andar_id')
        ->join('predios', 'predios.id', 'andares.predio_id')
        ->join('localidades', 'localidades.id', 'predios.localidade_id');

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);
