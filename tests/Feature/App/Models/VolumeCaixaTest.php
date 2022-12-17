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
use App\Models\VolumeCaixa;
use App\Pipes\VolumeCaixa\JoinLocalidade;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Exceptions
test('lança exception ao tentar criar volumes de caixa duplicados, isto é, com mesmo número', function () {
    $caixa = Caixa::factory()->create();

    expect(
        fn () => VolumeCaixa::factory(2)->create([
            'numero' => 10,
            'caixa_id' => $caixa->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar volume de caixa com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => VolumeCaixa::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',  'foo',            'Incorrect integer value'],    // não conversível em inteiro
    ['numero',  -1,               'Out of range value'],         // min 0
    ['numero',  4294967296,       'Out of range value'],         // max 4294967295
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => VolumeCaixa::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['caixa_id', 99999999, 'Cannot add or update a child row'], // não existente
    ['caixa_id', null,     'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('aceita campos em seus tamanhos máximos', function () {
    VolumeCaixa::factory()->create([
        'numero' => 4294967295,
        'descricao' => Str::random(255),
    ]);

    expect(VolumeCaixa::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    VolumeCaixa::factory()->create(['descricao' => null]);

    expect(VolumeCaixa::count())->toBe(1);
});

test('um volume de caixa pertente a uma caixa', function () {
    $volume_caixa = VolumeCaixa::factory()->for(Caixa::factory(), 'caixa')->create();

    $volume_caixa->load(['caixa']);

    expect($volume_caixa->caixa)->toBeInstanceOf(Caixa::class);
});

test('um volume de caixa possui muitos processos', function () {
    VolumeCaixa::factory()->hasProcessos(3)->create();

    $volume_caixa = VolumeCaixa::with('processos')->first();

    expect($volume_caixa->processos)->toHaveCount(3);
});

test('retorna os volumes das caixa pelo escopo search que busca a partir do início do texto no número do volume da caixa', function (string $termo, int $quantidade) {
    VolumeCaixa::factory()->create(['numero' => 99999999]);
    VolumeCaixa::factory()->create(['numero' => 77778888]);
    VolumeCaixa::factory()->create(['numero' => 77777777]);
    VolumeCaixa::factory()->create(['numero' => 55555555]);

    $query = Pipeline::make()
        ->send(VolumeCaixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 4],
    [7777, 2],
    [99999, 1],
    [777888, 0],
]);

test('retorna os volumes das caixa pelo escopo search que busca a partir do início do texto no número, ano e complemento da caixa', function (string $termo, int $quantidade) {
    Caixa::factory()->hasVolumes(2)->create(['numero' => 99999999, 'ano' => 55555,  'complemento' => 'aaaaaaaa']);
    Caixa::factory()->hasVolumes(3)->create(['numero' => 88888888, 'ano' => 44444,  'complemento' => 'ccccbbbb']);

    $query = Pipeline::make()
        ->send(VolumeCaixa::query())
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

test('retorna os volumes das caixa pelo escopo search que busca a partir do início do texto no número da prateleira', function (string $termo, int $quantidade) {
    Prateleira::factory()->has(Caixa::factory()->hasVolumes(2))->create(['numero' => 'aaaaaaaa']);
    Prateleira::factory()->has(Caixa::factory()->hasVolumes(3))->create(['numero' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(VolumeCaixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna os volumes das caixa pelo escopo search que busca a partir do início do texto no número da estante', function (string $termo, int $quantidade) {
    Estante::factory()
        ->has(Prateleira::factory()
            ->has(Caixa::factory()->hasVolumes(2)))->create(['numero' => 'aaaaaaaa']);
    Estante::factory()
        ->has(Prateleira::factory()
            ->has(Caixa::factory()->hasVolumes(3)))->create(['numero' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(VolumeCaixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna os volumes das caixa pelo escopo search que busca a partir do início do texto no número da sala', function (string $termo, int $quantidade) {
    Sala::factory()
        ->has(Estante::factory()
            ->has(Prateleira::factory()
                ->has(Caixa::factory()->hasVolumes(2))))->create(['numero' => 'aaaaaaaa']);
    Sala::factory()
        ->has(Estante::factory()
            ->has(Prateleira::factory()
                ->has(Caixa::factory()->hasVolumes(3))))->create(['numero' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(VolumeCaixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna os volumes das caixa pelo escopo search que busca a partir do início do texto no número e apelido do andar', function (string $termo, int $quantidade) {
    Andar::factory()
        ->has(Sala::factory()
            ->has(Estante::factory()
                ->has(Prateleira::factory()
                    ->has(Caixa::factory()->hasVolumes(2)))))
        ->create(['numero' => 99999999, 'apelido' => 'aaaaaaaa']);
    Andar::factory()
        ->has(Sala::factory()
            ->has(Estante::factory()
                ->has(Prateleira::factory()
                    ->has(Caixa::factory()->hasVolumes(3)))))
        ->create(['numero' => 88888888, 'apelido' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(VolumeCaixa::query())
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

test('retorna os volumes das caixa pelo escopo search que busca a partir do início do texto no nome do prédio', function (string $termo, int $quantidade) {
    Predio::factory()
        ->has(Andar::factory()
            ->has(Sala::factory()
                ->has(Estante::factory()
                    ->has(Prateleira::factory()
                        ->has(Caixa::factory()->hasVolumes(2))))), 'andares')
        ->create(['nome' => 'aaaaaaaa']);
    Predio::factory()
        ->has(Andar::factory()
            ->has(Sala::factory()
                ->has(Estante::factory()
                    ->has(Prateleira::factory()
                        ->has(Caixa::factory()->hasVolumes(3))))), 'andares')
        ->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(VolumeCaixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna os volumes das caixa pelo escopo search que busca a partir do início do texto no nome da localidade', function (string $termo, int $quantidade) {
    Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()
                        ->has(Prateleira::factory()
                            ->has(Caixa::factory()->hasVolumes(2))))), 'andares'))
        ->create(['nome' => 'aaaaaaaa']);

    Localidade::factory()
        ->has(Predio::factory()
            ->has(Andar::factory()
                ->has(Sala::factory()
                    ->has(Estante::factory()
                        ->has(Prateleira::factory()
                            ->has(Caixa::factory()->hasVolumes(3))))), 'andares'))
        ->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(VolumeCaixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('retorna os volumes das caixa pelo escopo search que busca a partir do início do texto no nome da localidade criadora', function (string $termo, int $quantidade) {
    Localidade::factory()->has(Caixa::factory()->hasVolumes(2), 'caixasCriadas')->create(['nome' => 'aaaaaaaa']);
    Localidade::factory()->has(Caixa::factory()->hasVolumes(3), 'caixasCriadas')->create(['nome' => 'bbbbbbbb']);

    $query = Pipeline::make()
        ->send(VolumeCaixa::query())
        ->through([JoinLocalidade::class])
        ->thenReturn();

    expect($query->search($termo)->count())->toBe($quantidade);
})->with([
    ['', 5],
    ['aaaa', 2],
    ['bbbb', 3],
]);

test('moverProcessos move os processos informados para determinado volume da caixa e altera o status de guarda dos processos para o status da caixa pai', function (bool $gp) {
    $caixa = Caixa::factory()->create(['guarda_permanente' => $gp]);
    $volume = VolumeCaixa::factory()->for($caixa, 'caixa')->create();
    $processo_1 = Processo::factory()->create(['guarda_permanente' => $gp]);
    $processo_2 = Processo::factory()->create(['guarda_permanente' => !$gp]);
    $processo_3 = Processo::factory()->create(['guarda_permanente' => !$gp]);

    $afetados = $volume->moverProcessos([
        apenasNumeros($processo_1->numero),
        apenasNumeros($processo_2->numero),
    ]);

    $processo_1->refresh();
    $processo_2->refresh();
    $processo_3->refresh();

    expect($afetados)->toBe(2)
        ->and($processo_1->volume_caixa_id)->toBe($volume->id)
        ->and($processo_1->guarda_permanente)->toBe($gp)
        ->and($processo_2->volume_caixa_id)->toBe($volume->id)
        ->and($processo_1->guarda_permanente)->toBe($gp)
        ->and($processo_3->volume_caixa_id)->not->toBe($volume->id)
        ->and($processo_3->guarda_permanente)->toBe(!$gp);
})->with([
    true,
    false,
]);
