<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Prateleira\JoinLocalidade;
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
test('lança exception ao tentar criar prateleiras duplicadas, isto é, com mesmo número e estante', function () {
    $estante = Estante::factory()->create();

    expect(
        fn () => Prateleira::factory(2)->create([
            'numero' => 100,
            'estante_id' => $estante->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar prateleira com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Prateleira::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',    null,             'cannot be null'],           // obrgatório
    ['numero',    Str::random(51),  'Data too long for column'], // máximo 51 caracteres
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Prateleira::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['estante_id', 99999999, 'Cannot add or update a child row'], // não existente
    ['estante_id', null,     'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('aceita campos em seus tamanhos máximos', function () {
    Prateleira::factory()->create([
        'numero' => Str::random(50),
        'descricao' => Str::random(255),
    ]);

    expect(Prateleira::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    Prateleira::factory()->create([
        'descricao' => null,
    ]);

    expect(Prateleira::count())->toBe(1);
});

test('zero é válido para o número da prateleira', function () {
    Prateleira::factory()->create(['numero' => 0]);

    $prateleira = Prateleira::first();

    expect($prateleira->numero)->toBe('0');
});

test('uma prateleira pertence a uma estante', function () {
    $prateleira = Prateleira::factory()->for(Estante::factory(), 'estante')->create();

    $prateleira->load(['estante']);

    expect($prateleira->estante)->toBeInstanceOf(Estante::class);
});

test('uma prateleira possui muitas caixas', function () {
    Prateleira::factory()->has(Caixa::factory(3), 'caixas')->create();

    $prateleira = Prateleira::with('caixas')->first();

    expect($prateleira->caixas)->toHaveCount(3);
});

test('retorna as prateleiras pelo escopo search que busca a partir do início do texto no número do prateleira', function (string $termo, int $quantidade) {
    Prateleira::factory()->create(['numero' => 'aaaaaaaa']);
    Prateleira::factory()->create(['numero' => 'ccccbbbb']);
    Prateleira::factory()->create(['numero' => 'cccccccc']);
    Prateleira::factory()->create(['numero' => 'dddddddd']);

    $query = Pipeline::make()
        ->send(Prateleira::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 4],
    ['ccc', 2],
    ['ddd', 1],
    ['ccbb', 0],
]);

test('retorna as prateleiras pelo escopo search que busca a partir do início do texto no número da estante', function (string $termo, int $quantidade) {
    Estante::factory()->hasPrateleiras(2)->create(['numero' => 'aaaaaaaa']);
    Estante::factory()->hasPrateleiras(3)->create(['numero' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Prateleira::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna as prateleiras pelo escopo search que busca a partir do início do texto no número da sala', function (string $termo, int $quantidade) {
    Sala::factory()->has(Estante::factory()->hasPrateleiras(2))->create(['numero' => 'aaaaaaaa']);
    Sala::factory()->has(Estante::factory()->hasPrateleiras(3))->create(['numero' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Prateleira::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna as prateleiras pelo escopo search que busca a partir do início do texto no número e apelido do andar', function (string $termo, int $quantidade) {
    Andar::factory()
        ->has(Sala::factory()
            ->has(Estante::factory()->hasPrateleiras(2)))
        ->create(['numero' => 99999999, 'apelido' => 'aaaaaaaa']);
    Andar::factory()
        ->has(Sala::factory()
            ->has(Estante::factory()->hasPrateleiras(3)))
        ->create(['numero' => 88888888, 'apelido' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Prateleira::query())
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

test('retorna as prateleiras pelo escopo search que busca a partir do início do texto no nome do prédio', function (string $termo, int $quantidade) {
    Predio::factory()
        ->has(Andar::factory()
            ->has(Sala::factory()
                ->has(Estante::factory()->hasPrateleiras(2))), 'andares')
        ->create(['nome' => 'aaaaaaaa']);
    Predio::factory()
        ->has(Andar::factory()
            ->has(Sala::factory()
                ->has(Estante::factory()->hasPrateleiras(3))), 'andares')
        ->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Prateleira::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna as prateleiras pelo escopo search que busca a partir do início do texto no nome da localidade', function (string $termo, int $quantidade) {
    Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()->hasPrateleiras(2))), 'andares'))
        ->create(['nome' => 'aaaaaaaa']);

    Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()->hasPrateleiras(3))), 'andares'))
        ->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Prateleira::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('método modeloPadrao retorna o modelo com os atributos esperados', function () {
    $prateleira = Prateleira::modeloPadrao();

    expect($prateleira->numero)->toBe(0)
        ->and($prateleira->descricao)->toBe('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado');
});
