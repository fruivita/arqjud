<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Andar;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use App\Pipes\Estante\JoinLocalidade;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Exceptions
test('lança exception ao tentar criar estantes duplicadas, isto é, com mesmo número', function () {
    $sala = Sala::factory()->create();

    expect(
        fn () => Estante::factory(2)->create([
            'numero' => '100-foo',
            'sala_id' => $sala->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar estante com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Estante::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',    null,             'cannot be null'],           // obrigatório
    ['numero',    Str::random(51),  'Data too long for column'], // máximo 50 caracteres
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Estante::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['sala_id', 99999999, 'Cannot add or update a child row'], // não existente
    ['sala_id', null,     'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('aceita campos em seus tamanhos máximos', function () {
    Estante::factory()->create([
        'numero' => Str::random(50),
        'descricao' => Str::random(255),
    ]);

    expect(Estante::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    Estante::factory()->create(['descricao' => null]);

    expect(Estante::count())->toBe(1);
});

test('zero é válido para o número da estante', function () {
    Estante::factory()->create(['numero' => 0]);

    $estante = Estante::first();

    expect($estante->numero)->toBe('0');
});

test('uma estante pertence a uma sala', function () {
    $estante = Estante::factory()->for(Sala::factory(), 'sala')->create();

    $estante->load(['sala']);

    expect($estante->sala)->toBeInstanceOf(Sala::class);
});

test('uma estante possui muitas prateleiras', function () {
    Estante::factory()->has(Prateleira::factory(3), 'prateleiras')->create();

    $estante = Estante::with('prateleiras')->first();

    expect($estante->prateleiras)->toHaveCount(3);
});

test('retorna as estantes pelo escopo search que busca a partir do início do texto no número do estante', function (string $termo, int $quantidade) {
    Estante::factory()->create(['numero' => 'aaaaaaaa']);
    Estante::factory()->create(['numero' => 'ccccbbbb']);
    Estante::factory()->create(['numero' => 'cccccccc']);
    Estante::factory()->create(['numero' => 'dddddddd']);

    $query = Pipeline::make()
        ->send(Estante::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 4],
    ['ccc', 2],
    ['ddd', 1],
    ['ccbb', 0],
]);

test('retorna as estantes pelo escopo search que busca a partir do início do texto no número da sala', function (string $termo, int $quantidade) {
    Sala::factory()->hasEstantes(2)->create(['numero' => 'aaaaaaaa']);
    Sala::factory()->hasEstantes(3)->create(['numero' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Estante::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna as estantes pelo escopo search que busca a partir do início do texto no número e apelido do andar', function (string $termo, int $quantidade) {
    Andar::factory()
        ->has(Sala::factory()->hasEstantes(2))
        ->create(['numero' => 99999999, 'apelido' => 'aaaaaaaa']);
    Andar::factory()
        ->has(Sala::factory()->hasEstantes(3))
        ->create(['numero' => 88888888, 'apelido' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Estante::query())
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

test('retorna as estantes pelo escopo search que busca a partir do início do texto no nome do prédio', function (string $termo, int $quantidade) {
    Predio::factory()
        ->has(Andar::factory()
            ->has(Sala::factory()->hasEstantes(2)), 'andares')
        ->create(['nome' => 'aaaaaaaa']);
    Predio::factory()
        ->has(Andar::factory()
            ->has(Sala::factory()->hasEstantes(3)), 'andares')
        ->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Estante::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna as estantes pelo escopo search que busca a partir do início do texto no nome da localidade', function (string $termo, int $quantidade) {
    Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()->hasEstantes(2)), 'andares'))
        ->create(['nome' => 'aaaaaaaa']);

    Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()->hasEstantes(3)), 'andares'))
        ->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Estante::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('método modeloPadrao retorna o modelo com os atributos esperados', function () {
    $estante = Estante::modeloPadrao();

    expect($estante->numero)->toBe(0)
        ->and($estante->descricao)->toBe('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado');
});

test('método criar cria uma estante como filha da sala infomada e também a prateleira padrão', function () {
    $sala = Sala::factory()->create();

    $salvo = Estante::criar('10', $sala, 'foo');

    $sala->load('estantes.prateleiras');

    $estante = $sala->estantes->first();
    $prateleira = $estante->prateleiras->first();

    expect($salvo)->toBeTrue()
        ->and($estante->numero)->toBe('10')
        ->and($estante->descricao)->toBe('foo')
        ->and($estante->sala_id)->toBe($sala->id)
        ->and($prateleira->numero)->toBe('0')
        ->and($prateleira->estante_id)->toBe($estante->id)
        ->and($prateleira->descricao)->toBe('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado');
});

test('método criar está protegido por transaction', function () {
    $sala = Sala::factory()->create();

    $database = DB::spy();

    Estante::criar('10', $sala, 'foo');

    $database->shouldHaveReceived('beginTransaction')->once();
    $database->shouldHaveReceived('commit')->once();
    $database->shouldNotReceive('rollBack');

    expect(Estante::count())->toBe(1)
        ->and(Prateleira::count())->toBe(1);
});

test('método criar faz rollBack em caso de falha', function () {
    $sala = Sala::factory()->create();

    $database = DB::spy();

    Prateleira::saving(fn () => throw new \RuntimeException());

    $salvo = Estante::criar('10', $sala, 'foo');

    $database->shouldHaveReceived('beginTransaction')->once();
    $database->shouldHaveReceived('rollBack')->once();
    $database->shouldNotReceive('commit');

    expect($salvo)->toBeFalse();
});

test('modelos não são persistidos devido ao rollBack no método criar', function () {
    $sala = Sala::factory()->create();

    Prateleira::saving(fn () => throw new \RuntimeException());

    Estante::criar('10', $sala, 'foo');

    expect(Estante::count())->toBe(0)
        ->and(Prateleira::count())->toBe(0);
});

test('registra falhas do método criar em log', function () {
    $sala = Sala::factory()->create();

    Prateleira::saving(fn () => throw new \RuntimeException());

    Log::spy();

    Estante::criar('10', $sala, 'foo');

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message) => $message === __('Falha na criação da estante'))
        ->once();
});
