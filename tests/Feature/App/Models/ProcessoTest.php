<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Processo\JoinLocalidade;
use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Processo;
use App\Models\Sala;
use App\Models\VolumeCaixa;
use Illuminate\Database\QueryException;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar processos duplicados, isto é, com mesmo número ou mesmo número antigo', function () {
    expect(
        fn () => Processo::factory(2)->create(['numero' => '123'])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Processo::factory(2)->create(['numero_antigo' => '321'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar processo com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Processo::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',            fn () => str_repeat('1', 21), 'Data too long for column'], // máximo 20 caracteres
    ['numero',            null,                         'cannot be null'],           // obrigatório
    ['numero_antigo',     fn () => str_repeat('1', 21), 'Data too long for column'], // máximo 20 caracteres
    ['arquivado_em',      '2000-02-31',                 'Incorrect date value'],     // data inexistente
    ['arquivado_em',      'foo',                        'Incorrect date value'],     // não conversível em date
    ['arquivado_em',      null,                         'cannot be null'],           // obrigatório
    ['guarda_permanente', 'foo',                        'Incorrect integer value'],  // não conversível em boolean
    ['guarda_permanente', null,                         'cannot be null'],           // obrigatório
    ['qtd_volumes',       4294967296,                   'Out of range value'],       // max 4294967295
    ['qtd_volumes',       null,                         'cannot be null'],           // obrigatório
    ['descricao',         Str::random(256),             'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Processo::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['volume_caixa_id', 99999999, 'Cannot add or update a child row'], // não existente
    ['volume_caixa_id', null,     'cannot be null'],                   // obrigatório
    ['processo_pai_id', 99999999, 'Cannot add or update a child row'], // não existente
]);

// Caminho feliz
test('aceita campos em seus tamanhos máximos', function () {
    Processo::factory()->create([
        'numero' => str_repeat('1', 20),
        'numero_antigo' => Str::random(20),
        'qtd_volumes' => 4294967295,
        'descricao' => Str::random(255),
    ]);

    expect(Processo::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    Processo::factory()->create([
        'numero_antigo' => null,
        'descricao' => null,
    ]);

    expect(Processo::count())->toBe(1);
});

test('processo pai é opcional', function () {
    Processo::factory()->create(['processo_pai_id' => null]);

    expect(Processo::count())->toBe(1);
});

test('máscaras de processo estão definidas', function () {
    expect(Processo::MASCARA_CNJ)->toBe('#######-##.####.#.##.####')
        ->and(Processo::MASCARA_V2)->toBe('####.##.##.######-#')
        ->and(Processo::MASCARA_V1)->toBe('##.#######-#');
});

test('um processo pertence a um volume de caixa', function () {
    $processo = Processo::factory()->for(VolumeCaixa::factory(), 'volumeCaixa')->create();

    $processo->load(['volumeCaixa']);

    expect($processo->volumeCaixa)->toBeInstanceOf(VolumeCaixa::class);
});

test('um processo possui um processo pai', function () {
    $processo = Processo::factory()->for(Processo::factory(), 'processoPai')->create();

    $processo->load(['processoPai']);

    expect($processo->processoPai)->toBeInstanceOf(Processo::class);
});

test('um processo possui muitos processos filho', function () {
    Processo::factory()->hasProcessosFilho(3)->create();

    $processo = Processo::with('processosFilho')->first();

    expect($processo->processosFilho)->toHaveCount(3);
});

test('um processo pode ter várias solicitações', function () {
    Processo::factory()->hasSolicitacoes(3)->create();

    $processo = Processo::with('solicitacoes')->first();

    expect($processo->solicitacoes)->toHaveCount(3);
});


test('retorna os processos pelo escopo search que busca a partir do início do texto no número, do número antigo e da quantidade de volumes da caixa', function (string $termo, int $quantidade) {
    Processo::factory()->create(['numero' => '99999999', 'numero_antigo' => '55555555', 'qtd_volumes' => 11111111]);
    Processo::factory()->create(['numero' => '77778888', 'numero_antigo' => '44444444', 'qtd_volumes' => 11111222]);
    Processo::factory()->create(['numero' => '77777777', 'numero_antigo' => '33333333', 'qtd_volumes' => 11111333]);
    Processo::factory()->create(['numero' => '66666666', 'numero_antigo' => '33333222', 'qtd_volumes' => 11111444]);

    $query = app(Pipeline::class)
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 4],
    [99999, 1],
    [33333, 2],
    [11111, 4],
    [777888, 0],
]);

test('retorna os processos pelo escopo search que busca a partir do início do texto no número do volume da caixa', function (string $termo, int $quantidade) {
    VolumeCaixa::factory()->hasProcessos(2)->create(['numero' => 99999999]);
    VolumeCaixa::factory()->hasProcessos(3)->create(['numero' => 77778888]);

    $query = app(Pipeline::class)
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    [7777, 3],
    [99999, 2],
]);

test('retorna os processos pelo escopo search que busca a partir do início do texto no número, ano e complemento da caixa', function (string $termo, int $quantidade) {
    Caixa::factory()
        ->has(VolumeCaixa::factory()->hasProcessos(2), 'volumes')
        ->create(['numero' => 99999999, 'ano' => 55555,  'complemento' => 'aaaaaaaa']);
    Caixa::factory()
        ->has(VolumeCaixa::factory()->hasProcessos(3), 'volumes')
        ->create(['numero' => 88888888, 'ano' => 44444,  'complemento' => 'ccccbbbb']);

    $query = app(Pipeline::class)
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    [55555, 2],
    [99999, 2],
    ['cccc', 3],
    [44444, 3],
    ['ccbb', 0],
]);

test('retorna os processos pelo escopo search que busca a partir do início do texto no número da prateleira', function (string $termo, int $quantidade) {
    Prateleira::factory()
        ->has(Caixa::factory()
            ->has(VolumeCaixa::factory()->hasProcessos(2), 'volumes'))
        ->create(['numero' => 'aaaaaaaa']);
    Prateleira::factory()
        ->has(Caixa::factory()
            ->has(VolumeCaixa::factory()->hasProcessos(3), 'volumes'))
        ->create(['numero' => 'bbbbbbbb']);

    $query = app(Pipeline::class)
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna os processos pelo escopo search que busca a partir do início do texto no número da estante', function (string $termo, int $quantidade) {
    Estante::factory()
        ->has(Prateleira::factory()
            ->has(Caixa::factory()
                ->has(VolumeCaixa::factory()->hasProcessos(2), 'volumes')))
        ->create(['numero' => 'aaaaaaaa']);
    Estante::factory()
        ->has(Prateleira::factory()
            ->has(Caixa::factory()
                ->has(VolumeCaixa::factory()->hasProcessos(3), 'volumes')))
        ->create(['numero' => 'bbbbbbbb']);

    $query = app(Pipeline::class)
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna os processos pelo escopo search que busca a partir do início do texto no número da sala', function (string $termo, int $quantidade) {
    Sala::factory()
        ->has(Estante::factory()
            ->has(Prateleira::factory()
                ->has(Caixa::factory()
                    ->has(VolumeCaixa::factory()->hasProcessos(2), 'volumes'))))
        ->create(['numero' => 'aaaaaaaa']);
    Sala::factory()
        ->has(Estante::factory()
            ->has(Prateleira::factory()
                ->has(Caixa::factory()
                    ->has(VolumeCaixa::factory()->hasProcessos(3), 'volumes'))))
        ->create(['numero' => 'bbbbbbbb']);

    $query = app(Pipeline::class)
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna os processos pelo escopo search que busca a partir do início do texto no número e apelido do andar', function (string $termo, int $quantidade) {
    Andar::factory()
        ->has(Sala::factory()
            ->has(Estante::factory()
                ->has(Prateleira::factory()
                    ->has(Caixa::factory()
                        ->has(VolumeCaixa::factory()->hasProcessos(2), 'volumes')))))
        ->create(['numero' => 99999999, 'apelido' => 'aaaaaaaa']);
    Andar::factory()
        ->has(Sala::factory()
            ->has(Estante::factory()
                ->has(Prateleira::factory()
                    ->has(Caixa::factory()
                        ->has(VolumeCaixa::factory()->hasProcessos(3), 'volumes')))))
        ->create(['numero' => 88888888, 'apelido' => 'bbbbbbbb']);

    $query = app(Pipeline::class)
        ->send(Processo::query())
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

test('retorna os processos pelo escopo search que busca a partir do início do texto no nome do prédio', function (string $termo, int $quantidade) {
    Predio::factory()
        ->has(Andar::factory()
            ->has(Sala::factory()
                ->has(Estante::factory()
                    ->has(Prateleira::factory()
                        ->has(Caixa::factory()
                            ->has(VolumeCaixa::factory()->hasProcessos(2), 'volumes'))))), 'andares')
        ->create(['nome' => 'aaaaaaaa']);
    Predio::factory()
        ->has(Andar::factory()
            ->has(Sala::factory()
                ->has(Estante::factory()
                    ->has(Prateleira::factory()
                        ->has(Caixa::factory()
                            ->has(VolumeCaixa::factory()->hasProcessos(3), 'volumes'))))), 'andares')
        ->create(['nome' => 'bbbbbbbb']);

    $query = app(Pipeline::class)
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna os processos pelo escopo search que busca a partir do início do texto no nome da localidade', function (string $termo, int $quantidade) {
    Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()
                        ->has(Prateleira::factory()
                            ->has(Caixa::factory()
                                ->has(VolumeCaixa::factory()->hasProcessos(2), 'volumes'))))), 'andares'))
        ->create(['nome' => 'aaaaaaaa']);

    Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()
                        ->has(Prateleira::factory()
                            ->has(Caixa::factory()
                                ->has(VolumeCaixa::factory()->hasProcessos(3), 'volumes'))))), 'andares'))
        ->create(['nome' => 'bbbbbbbb']);

    $query = app(Pipeline::class)
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna os processos pelo escopo search que busca a partir do início do texto no nome da localidade criadora', function (string $termo, int $quantidade) {
    Localidade::factory()
        ->has(Caixa::factory()
            ->has(VolumeCaixa::factory()->hasProcessos(2), 'volumes'), 'caixasCriadas')
        ->create(['nome' => 'aaaaaaaa']);
    Localidade::factory()
        ->has(Caixa::factory()
            ->has(VolumeCaixa::factory()->hasProcessos(3), 'volumes'), 'caixasCriadas')
        ->create(['nome' => 'bbbbbbbb']);

    $query = app(Pipeline::class)
        ->send(Processo::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('aplica a máscara correta ao processo e retorna o valor esperado', function (mixed $sem_mascara, mixed $com_mascara) {
    expect(Processo::aplicarMascaraProcesso($sem_mascara))->toBe($com_mascara);
})->with([
    ['1111111111', '11.1111111-1'],
    ['111111111111111', '1111.11.11.111111-1'],
    ['11111111111111111111', '1111111-11.1111.1.11.1111'],
    ['111111', '111111'],
    ['', null],
    [null, null],
]);
