<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Sala\JoinLocalidade;
use App\Models\Andar;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MichaelRubel\EnhancedPipeline\Pipeline;
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
    Sala::factory()->hasEstantes(3)->create();

    $sala = Sala::with('estantes')->first();

    expect($sala->estantes)->toHaveCount(3);
});

test('retorna as salas pelo escopo search que busca a partir do início do texto no número do sala', function (string $termo, int $quantidade) {
    Sala::factory()->create(['numero' => 'aaaaaaaa']);
    Sala::factory()->create(['numero' => 'ccccbbbb']);
    Sala::factory()->create(['numero' => 'cccccccc']);
    Sala::factory()->create(['numero' => 'dddddddd']);

    $query = Pipeline::make()
        ->send(Sala::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 4],
    ['ccc', 2],
    ['ddd', 1],
    ['ccbb', 0],
]);

test('retorna as salas pelo escopo search que busca a partir do início do texto no número e apelido do andar', function (string $termo, int $quantidade) {
    Andar::factory()->hasSalas(2)->create(['numero' => 99999999, 'apelido' => 'aaaaaaaa']);
    Andar::factory()->hasSalas(3)->create(['numero' => 88888888, 'apelido' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Sala::query())
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

test('retorna as salas pelo escopo search que busca a partir do início do texto no nome do prédio', function (string $termo, int $quantidade) {
    Predio::factory()->has(Andar::factory()->hasSalas(2), 'andares')->create(['nome' => 'aaaaaaaa']);
    Predio::factory()->has(Andar::factory()->hasSalas(3), 'andares')->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Sala::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

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

    $query = Pipeline::make()
        ->send(Sala::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('método criar cria uma sala como filha do andar infomado e também a estante e a prateleira padrão', function () {
    $andar = Andar::factory()->create();

    $salvo = Sala::criar(
        '10',
        $andar->id,
        'foo'
    );

    $andar->load('salas.estantes.prateleiras');

    $sala = $andar->salas->first();
    $estante = $sala->estantes->first();
    $prateleira = $estante->prateleiras->first();

    expect($salvo)->toBeTrue()
        ->and($sala->numero)->toBe('10')
        ->and($sala->descricao)->toBe('foo')
        ->and($sala->andar_id)->toBe($andar->id)
        ->and($estante->numero)->toBe('0')
        ->and($estante->sala_id)->toBe($sala->id)
        ->and($estante->descricao)->toBe('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado')
        ->and($prateleira->numero)->toBe('0')
        ->and($prateleira->estante_id)->toBe($estante->id)
        ->and($prateleira->descricao)->toBe('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado');
});

test('método criar está protegido por transaction', function () {
    $andar = Andar::factory()->create();

    $database = DB::spy();

    Sala::criar(
        '10',
        $andar->id,
        'foo'
    );

    $database->shouldHaveReceived('beginTransaction')->once();
    $database->shouldHaveReceived('commit')->once();
    $database->shouldNotReceive('rollBack');

    expect(Sala::count())->toBe(1)
        ->and(Estante::count())->toBe(1)
        ->and(Prateleira::count())->toBe(1);
});

test('método criar faz rollBack em caso de falha', function () {
    $andar = Andar::factory()->create();

    $database = DB::spy();

    Prateleira::saving(fn () => throw new \RuntimeException());

    $salvo = Sala::criar(
        '10',
        $andar->id,
        'foo'
    );

    $database->shouldHaveReceived('beginTransaction')->once();
    $database->shouldHaveReceived('rollBack')->once();
    $database->shouldNotReceive('commit');

    expect($salvo)->toBeFalse();
});

test('modelo não são persistidos devido ao rollBack no método criar', function () {
    $andar = Andar::factory()->create();

    Prateleira::saving(fn () => throw new \RuntimeException());

    Sala::criar(
        '10',
        $andar->id,
        'foo'
    );

    expect(Sala::count())->toBe(0)
        ->and(Estante::count())->toBe(0)
        ->and(Prateleira::count())->toBe(0);
});

test('registra falhas do método criar em log', function () {
    $andar = Andar::factory()->create();

    Prateleira::saving(fn () => throw new \RuntimeException());

    Log::spy();

    Sala::criar(
        '10',
        $andar->id,
        'foo'
    );

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message) => $message === __('Falha na criação da sala'))
        ->once();
});
