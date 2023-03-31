<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Processo;
use App\Models\Sala;
use App\Models\TipoProcesso;
use App\Pipes\Caixa\JoinLocalidade;
use App\Pipes\Caixa\JoinTipoProcesso;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Exceptions
test('lança exception ao tentar criar caixas duplicados, isto é, com mesmo ano, número, se é guarda permanente, local de criação, tipo de processo e complemento', function () {
    $localidade = Localidade::factory()->create();
    $tipo_processo = TipoProcesso::factory()->create();

    expect(
        fn () => Caixa::factory(2)->create([
            'numero' => 100,
            'ano' => 2020,
            'guarda_permanente' => true,
            'complemento' => 'foo',
            'localidade_criadora_id' => $localidade->id,
            'tipo_processo_id' => $tipo_processo->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar caixa com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Caixa::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',            -1,               'Out of range'],             // min 0
    ['numero',            4294967296,       'Out of range'],             // max 4294967295
    ['numero',            'foo',            'Incorrect integer value'],  // não conversível em inteiro
    ['ano',               -1,               'Out of range value'],       // min 0
    ['ano',               65536,            'Out of range value'],       // max 65536
    ['ano',               'foo',            'Incorrect integer value'],  // não conversível em inteiro
    ['guarda_permanente', 'foo',            'Incorrect integer value'],  // não conversível em boolean
    ['guarda_permanente', null,             'cannot be null'],           // obrigatório
    ['complemento',       Str::random(51),  'Data too long for column'], // máximo 50 caracteres
    ['descricao',         Str::random(256), 'Data too long for column'], // máximo 255 caracteres
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
    ['tipo_processo_id',       99999999, 'Cannot add or update a child row'], // não existente
    ['tipo_processo_id',       null,     'cannot be null'],                   // obrigatório
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

test('uma caixa armazena um tipo de processo', function () {
    $caixa = Caixa::factory()->for(TipoProcesso::factory(), 'tipoProcesso')->create();

    $caixa->load(['tipoProcesso']);

    expect($caixa->tipoProcesso)->toBeInstanceOf(TipoProcesso::class);
});

test('uma caixa possui muitos processos', function () {
    Caixa::factory()->hasProcessos(3)->create();

    $caixa = Caixa::with('processos')->first();

    expect($caixa->processos)->toHaveCount(3);
});

test('retorna as caixas pelo escopo search que busca a partir do início do texto no número, ano e complemento da caixa', function (string $termo, int $quantidade) {
    Caixa::factory()->create(['numero' => 99999999, 'ano' => 55555,  'complemento' => 'aaaaaaaa']);
    Caixa::factory()->create(['numero' => 88888888, 'ano' => 44444,  'complemento' => 'ccccbbbb']);
    Caixa::factory()->create(['numero' => 77777777, 'ano' => 33333,  'complemento' => 'cccccccc']);
    Caixa::factory()->create(['numero' => 55555555, 'ano' => 22222,  'complemento' => 'dddddddd']);

    $query = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class, JoinTipoProcesso::class])
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
        ->through([JoinLocalidade::class, JoinTipoProcesso::class])
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
        ->through([JoinLocalidade::class, JoinTipoProcesso::class])
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
        ->through([JoinLocalidade::class, JoinTipoProcesso::class])
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
        ->through([JoinLocalidade::class, JoinTipoProcesso::class])
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
        ->through([JoinLocalidade::class, JoinTipoProcesso::class])
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
        ->through([JoinLocalidade::class, JoinTipoProcesso::class])
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
        ->through([JoinLocalidade::class, JoinTipoProcesso::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna as caixas pelo escopo search que busca a partir do início do texto no nome do tipo de processo', function (string $termo, int $quantidade) {
    TipoProcesso::factory()->hasCaixas(2)->create(['nome' => 'aaaaaaaa']);
    TipoProcesso::factory()->hasCaixas(3)->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class, JoinTipoProcesso::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('método atualizar atualiza os dados da caixa', function () {
    $localidade = Localidade::factory()->create();
    $tipo_processo = TipoProcesso::factory()->create();
    $caixa = Caixa::factory()->create();

    $caixa->numero = 500;
    $caixa->ano = 2000;
    $caixa->guarda_permanente = true;
    $caixa->complemento = 'foo';
    $caixa->descricao = 'foo bar';
    $caixa->localidade_criadora_id = $localidade->id;
    $caixa->tipo_processo_id = $tipo_processo->id;

    $salvo = $caixa->atualizar();

    $caixa->refresh();

    expect($salvo)->toBeTrue()
        ->and($caixa->numero)->toBe(500)
        ->and($caixa->ano)->toBe(2000)
        ->and($caixa->guarda_permanente)->toBeTrue()
        ->and($caixa->complemento)->toBe('foo')
        ->and($caixa->descricao)->toBe('foo bar')
        ->and($caixa->localidade_criadora_id)->toBe($localidade->id)
        ->and($caixa->tipo_processo_id)->toBe($tipo_processo->id);
});

test('método atualizar atualiza o status de guarda permanente de todos os processos da caixa', function (bool $gp) {
    $caixa = Caixa::factory()
        ->hasProcessos(3, ['guarda_permanente' => !$gp])
        ->create();

    Processo::factory(2)->create(['guarda_permanente' => !$gp]); // não serão afetados

    $caixa->guarda_permanente = $gp;

    $caixa->atualizar();

    expect(Processo::where('guarda_permanente', $gp)->count())->toBe(3)
        ->and(Processo::where('guarda_permanente', !$gp)->count())->toBe(2);
})->with([
    true,
    false,
]);

test('método atualizar está protegido por transaction', function () {
    $caixa = Caixa::factory()->create();

    $database = DB::spy();

    $salvo = $caixa->atualizar();

    $database->shouldHaveReceived('beginTransaction')->once();
    $database->shouldHaveReceived('commit')->once();
    $database->shouldNotReceive('rollBack');

    expect($salvo)->toBeTrue();
});

test('método atualizar faz rollBack em caso de falha', function () {
    $caixa = Caixa::factory()->create();
    $database = DB::spy();
    Caixa::updated(fn () => throw new \RuntimeException());

    $caixa->guarda_permanente = !$caixa->guarda_permanente;
    $salvo = $caixa->atualizar();

    $database->shouldHaveReceived('beginTransaction')->once();
    $database->shouldHaveReceived('rollBack')->once();
    $database->shouldNotReceive('commit');

    expect($salvo)->toBeFalse();
});

test('alterações não são persistidas devido ao rollBack no método atualizar', function () {
    $caixa = Caixa::factory()
        ->hasProcessos(3, ['guarda_permanente' => true])
        ->create(['guarda_permanente' => true]);
    Caixa::updated(fn () => throw new \RuntimeException());

    $caixa->guarda_permanente = false;
    $caixa->atualizar();

    expect(Processo::where('guarda_permanente', true)->count())->toBe(3)
        ->and(Processo::where('guarda_permanente', false)->count())->toBe(0)
        ->and(Caixa::where('guarda_permanente', true)->count())->toBe(1)
        ->and(Caixa::where('guarda_permanente', false)->count())->toBe(0);
});

test('registra falhas do método atualizar em log', function () {
    $caixa = Caixa::factory()->create();
    Caixa::updated(fn () => throw new \RuntimeException());
    Log::spy();

    $caixa->guarda_permanente = !$caixa->guarda_permanente;
    $caixa->atualizar();

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message) => $message === __('Falha na atualização da caixa'))
        ->once();
});

test('moverProcessos move os processos informados para determinada caixa e altera o status de guarda dos processos para o status da caixa', function (bool $gp) {
    $caixa = Caixa::factory()->create(['guarda_permanente' => $gp]);
    $processo_1 = Processo::factory()->create(['guarda_permanente' => $gp]);
    $processo_2 = Processo::factory()->create(['guarda_permanente' => !$gp]);
    $processo_3 = Processo::factory()->create(['guarda_permanente' => !$gp]);

    $afetados = $caixa->moverProcessos([
        apenasNumeros($processo_1->numero),
        apenasNumeros($processo_2->numero),
    ]);

    $processo_1->refresh();
    $processo_2->refresh();
    $processo_3->refresh();

    expect($afetados)->toBe(2)
        ->and($processo_1->caixa_id)->toBe($caixa->id)
        ->and($processo_1->guarda_permanente)->toBe($gp)
        ->and($processo_2->caixa_id)->toBe($caixa->id)
        ->and($processo_1->guarda_permanente)->toBe($gp)
        ->and($processo_3->caixa_id)->not->toBe($caixa->id)
        ->and($processo_3->guarda_permanente)->toBe(!$gp);
})->with([
    true,
    false,
]);
