<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Caixa\JoinLocalidade;
use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Exceptions
test('lança exception ao tentar criar caixas duplicados, isto é, com mesmo ano, número, se é guarda permanente, local de criação e complemento', function () {
    $localidade = Localidade::factory()->create();

    expect(
        fn () => Caixa::factory(2)->create([
            'numero' => 100,
            'ano' => 2020,
            'guarda_permanente' => true,
            'complemento' => 'foo',
            'localidade_criadora_id' => $localidade->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar caixa com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Caixa::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',            -1,               'Out of range'],              // min 0
    ['numero',            4294967296,       'Out of range'],              // max 4294967295
    ['numero',            'foo',            'Incorrect integer value'],   // não conversível em inteiro
    ['ano',               -1,               'Out of range value'],        // min 0
    ['ano',               65536,            'Out of range value'],        // max 65536
    ['ano',               'foo',            'Incorrect integer value'],   // não conversível em inteiro
    ['guarda_permanente', 'foo',            'Incorrect integer value'],   // não conversível em boolean
    ['guarda_permanente', null,             'cannot be null'],            // obrigatório
    ['complemento',       Str::random(51),  'Data too long for column'],  // máximo 50 caracteres
    ['descricao',         Str::random(256), 'Data too long for column'],  // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Caixa::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['prateleira_id',          99999999, 'Cannot add or update a child row'], // não existente
    ['prateleira_id',          null,     'cannot be null'],                   // obrigatório
    ['localidade_criadora_id', 99999999, 'Cannot add or update a child row'], // não existente
    ['localidade_criadora_id', null,     'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('aceita campos em seus tamanhos máximos', function () {
    Caixa::factory()->create([
        'numero' => 4294967295,
        'ano' => 65535,
        'complemento' => Str::random(50),
        'descricao' => Str::random(255),
    ]);

    expect(Caixa::count())->toBe(1);
});

test('uma caixa só pode ser criada por uma localidade', function () {
    $caixa = Caixa::factory()->for(Localidade::factory(), 'localidadeCriadora')->create();

    $caixa->load(['localidadeCriadora']);

    expect($caixa->localidadeCriadora)->toBeInstanceOf(Localidade::class);
});

test('uma caixa pertente a uma prateleira', function () {
    $caixa = Caixa::factory()->for(Prateleira::factory(), 'prateleira')->create();

    $caixa->load(['prateleira']);

    expect($caixa->prateleira)->toBeInstanceOf(Prateleira::class);
});

test('uma caixa possui muitos volumes de caixa', function () {
    Caixa::factory()->hasVolumes(3)->create();

    $caixa = Caixa::with('volumes')->first();

    expect($caixa->volumes)->toHaveCount(3);
});

test('retorna as caixas pelo escopo search que busca a partir do início do texto no número, ano e complemento da caixa', function (string $termo, int $quantidade) {
    Caixa::factory()->create(['numero' => 99999999, 'ano' => 55555,  'complemento' => 'aaaaaaaa']);
    Caixa::factory()->create(['numero' => 88888888, 'ano' => 44444,  'complemento' => 'ccccbbbb']);
    Caixa::factory()->create(['numero' => 77777777, 'ano' => 33333,  'complemento' => 'cccccccc']);
    Caixa::factory()->create(['numero' => 55555555, 'ano' => 22222,  'complemento' => 'dddddddd']);

    $query = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 4],
    ['ccc', 2],
    [55555, 2],
    [99999, 1],
    [22222, 1],
    ['ccbb', 0],
]);

test('retorna as caixas pelo escopo search que busca a partir do início do texto no número da prateleira', function (string $termo, int $quantidade) {
    Prateleira::factory()->hasCaixas(2)->create(['numero' => 'aaaaaaaa']);
    Prateleira::factory()->hasCaixas(3)->create(['numero' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna as caixas pelo escopo search que busca a partir do início do texto no número da estante', function (string $termo, int $quantidade) {
    Estante::factory()->has(Prateleira::factory()->hasCaixas(2))->create(['numero' => 'aaaaaaaa']);
    Estante::factory()->has(Prateleira::factory()->hasCaixas(3))->create(['numero' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna as caixas pelo escopo search que busca a partir do início do texto no número da sala', function (string $termo, int $quantidade) {
    Sala::factory()
        ->has(Estante::factory()
            ->has(Prateleira::factory()->hasCaixas(2)))->create(['numero' => 'aaaaaaaa']);
    Sala::factory()
        ->has(Estante::factory()
            ->has(Prateleira::factory()->hasCaixas(3)))->create(['numero' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna as caixas pelo escopo search que busca a partir do início do texto no número e apelido do andar', function (string $termo, int $quantidade) {
    Andar::factory()
        ->has(Sala::factory()
            ->has(Estante::factory()
                ->has(Prateleira::factory()->hasCaixas(2))))
        ->create(['numero' => 99999999, 'apelido' => 'aaaaaaaa']);
    Andar::factory()
        ->has(Sala::factory()
            ->has(Estante::factory()
                ->has(Prateleira::factory()->hasCaixas(3))))
        ->create(['numero' => 88888888, 'apelido' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
    [999999, 2],
    [888888, 3],
]);

test('retorna as caixas pelo escopo search que busca a partir do início do texto no nome do prédio', function (string $termo, int $quantidade) {
    Predio::factory()
        ->has(Andar::factory()
            ->has(Sala::factory()
                ->has(Estante::factory()
                    ->has(Prateleira::factory()->hasCaixas(2)))), 'andares')
        ->create(['nome' => 'aaaaaaaa']);
    Predio::factory()
        ->has(Andar::factory()
            ->has(Sala::factory()
                ->has(Estante::factory()
                    ->has(Prateleira::factory()->hasCaixas(3)))), 'andares')
        ->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna as caixas pelo escopo search que busca a partir do início do texto no nome da localidade', function (string $termo, int $quantidade) {
    Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()
                        ->has(Prateleira::factory()->hasCaixas(2)))), 'andares'))
        ->create(['nome' => 'aaaaaaaa']);

    Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()
                        ->has(Prateleira::factory()->hasCaixas(3)))), 'andares'))
        ->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna as caixas pelo escopo search que busca a partir do início do texto no nome da localidade criadora', function (string $termo, int $quantidade) {
    Localidade::factory()->hasCaixasCriadas(2)->create(['nome' => 'aaaaaaaa']);
    Localidade::factory()->hasCaixasCriadas(3)->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);
