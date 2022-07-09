<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Estante;
use App\Models\Prateleira;
use App\Models\Sala;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar estantes duplicadas, isto é, com mesmo número/apelido', function () {
    $sala = Sala::factory()->create();

    expect(
        fn () => Estante::factory(2)->create([
            'numero' => 100,
            'sala_id' => $sala->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Estante::factory(2)->create([
            'apelido' => '100',
            'sala_id' => $sala->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar estante com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Estante::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',    -1,               'Out of range'],             // min 0
    ['numero',    4294967296,       'Out of range'],             // max 4294967295
    ['numero',    'foo',            'Incorrect integer value'],  // não conversível em inteiro
    ['apelido',   Str::random(101), 'Data too long for column'], // máximo 100 caracteres
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Estante::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['sala_id', 10,   'Cannot add or update a child row'], // não existente
    ['sala_id', null, 'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('cria muitas estantes', function () {
    Estante::factory(30)->create();

    expect(Estante::count())->toBe(30);
});

test('estantes com apelido null não são consideradas duplicadas', function () {
    $sala = Sala::factory()->create();

    Estante::factory()->for($sala)->create(['apelido' => null]);
    Estante::factory()->for($sala)->create(['apelido' => null]);
    Estante::factory()->for($sala)->create(['apelido' => '10']);

    $sala->load(['estantes' => function ($query) {
        $query->whereNull('apelido');
    }]);

    expect($sala->estantes)->toHaveCount(2);
});

test('aceita campos em seus tamanhos mínimos', function () {
    Estante::factory()->create(['numero' => 0]);

    expect(Estante::count())->toBe(1);
});

test('aceita campos em seus tamanhos máximos', function () {
    Estante::factory()->create([
        'numero' => 4294967295,
        'apelido' => Str::random(100),
        'descricao' => Str::random(255),
    ]);

    expect(Estante::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    Estante::factory()->create([
        'apelido' => null,
        'descricao' => null,
    ]);

    expect(Estante::count())->toBe(1);
});

test('zero é válido para o número da estante', function () {
    Estante::factory()->create(['numero' => 0]);

    $estante = Estante::first();

    expect($estante->numero)->toBe(0);
});

test('método modeloPadrao retorna o modelo com os atributos esperados', function () {
    $estante = Estante::modeloPadrao();

    expect($estante->numero)->toBe(0)
    ->and($estante->apelido)->toBe(__('Não informada'))
    ->and($estante->descricao)->toBe(__('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado'));
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

test('método linksPais retorna as rotas de edição ordenadas do pai mais distante para o mais próximo se root for false', function () {
    Estante::factory()->create();

    $estante = Estante::hierarquia()->first();

    expect($estante->linksPais(false)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $estante->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $estante->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $estante->andar_id),
        __('Sala') => route('arquivamento.cadastro.sala.edit', $estante->sala_id),
    ]);
});

test('método linksPais retorna as rotas de edição, inclusise do elemento root, ordenadas do pai mais distante para o mais próximo se root for true', function () {
    Estante::factory()->create();

    $estante = Estante::hierarquia()->first();

    expect($estante->linksPais(true)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $estante->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $estante->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $estante->andar_id),
        __('Sala') => route('arquivamento.cadastro.sala.edit', $estante->sala_id),
        __('Estante') => route('arquivamento.cadastro.estante.edit', $estante->id),
    ]);
});

test('método linksPais retorna os links baseados nos dados hierárquicos presentes no modelo ou, se não presentes, os busca no banco de dados', function () {
    Estante::factory()->create();

    $estante = Estante::first();
    $estante->load('sala.andar.predio');

    expect($estante->linksPais(true)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $estante->sala->andar->predio->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $estante->sala->andar->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $estante->sala->andar_id),
        __('Sala') => route('arquivamento.cadastro.sala.edit', $estante->sala->id),
        __('Estante') => route('arquivamento.cadastro.estante.edit', $estante->id),
    ]);
});

test('método hierarquia retorna todos as estantes com o id e nome/número da respectiva sala, andar, prédio e localidade e o respectivo número de prateleiras', function () {
    Estante::factory()->create(['numero' => 10]);
    Estante::factory()->has(Prateleira::factory(1), 'prateleiras')->create(['numero' => 20]);
    Estante::factory()->has(Prateleira::factory(2), 'prateleiras')->create(['numero' => 30]);

    $estantes = Estante::hierarquia()->get();

    $estante_10 = $estantes->firstWhere('numero', 10);
    $estante_20 = $estantes->firstWhere('numero', 20);
    $estante_30 = $estantes->firstWhere('numero', 30);

    expect($estantes)->toHaveCount(3)
    ->and(empty($estante_10->localidade_id))->toBeFalse()
    ->and(empty($estante_10->localidade_nome))->toBeFalse()
    ->and(empty($estante_10->predio_id))->toBeFalse()
    ->and(empty($estante_10->predio_nome))->toBeFalse()
    ->and(empty($estante_10->andar_id))->toBeFalse()
    ->and(empty($estante_10->andar_apelido))->toBeFalse()
    ->and(empty($estante_10->andar_numero))->toBeFalse()
    ->and(empty($estante_10->sala_id))->toBeFalse()
    ->and(empty($estante_10->sala_numero))->toBeFalse()
    ->and($estante_10->prateleiras_count)->toBe(0)
    ->and($estante_20->prateleiras_count)->toBe(1)
    ->and($estante_30->prateleiras_count)->toBe(2);
});

test('método paraHumano retorna os dados em formato para leitura humana', function () {
    Estante::factory()->create(['numero' => 10]);

    $estante = Estante::hierarquia()->first();

    expect($estante->para_humano)->toBe(10);
});

test('método paraHumano retorna "Não informada" se o número da estante for zero', function () {
    Estante::factory()->create(['numero' => 0]);

    $estante = Estante::hierarquia()->first();

    expect($estante->para_humano)->toBe(__('Não informada'));
});
