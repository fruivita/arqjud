<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Localidade;
use App\Models\Predio;
use App\Pipes\Predio\JoinLocalidade;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use MichaelRubel\EnhancedPipeline\Pipeline;

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

test('um prédio possui muitos andares', function () {
    Predio::factory()->hasAndares(3)->create();

    $predio = Predio::with('andares')->first();

    expect($predio->andares)->toHaveCount(3);
});

test('retorna os prédios pelo escopo search que busca a partir do início do texto no nome do prédio', function (string $termo, int $quantidade) {
    Predio::factory()->create(['nome' => 'aaaaaaaa']);
    Predio::factory()->create(['nome' => 'ccccbbbb']);
    Predio::factory()->create(['nome' => 'cccccccc']);
    Predio::factory()->create(['nome' => 'dddddddd']);

    $query = Pipeline::make()
        ->send(Predio::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 4],
    ['ccc', 2],
    ['ddd', 1],
    ['ccbb', 0],
]);

test('retorna os prédios pelo escopo search que busca a partir do início do texto no nome da localidade pai', function (string $termo, int $quantidade) {
    Localidade::factory()->hasPredios(2)->create(['nome' => 'aaaaaaaa']);
    Localidade::factory()->hasPredios(3)->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Predio::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('Prédio usa trait', function () {
    expect(
        collect(class_uses(Predio::class))
            ->has([
                \App\Models\Trait\Auditavel::class,
            ])
    )->toBeTrue();
});
