<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Andar;
use App\Models\Predio;
use App\Models\Sala;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar andares duplicados, isto é, com mesmo numero/apelido e prédio', function () {
    $predio = Predio::factory()->create();

    expect(
        fn () => Andar::factory(2)->create([
            'numero' => 100,
            'predio_id' => $predio->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Andar::factory(2)->create([
            'apelido' => 100,
            'predio_id' => $predio->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar andar com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Andar::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',    -2147483649,      'Out of range'],             // min -2147483648
    ['numero',    2147483648,       'Out of range'],             // max 2147483647
    ['numero',    'foo',            'Incorrect integer value'],  // não conversível em inteiro
    ['numero',    null,             'cannot be null'],           // obrigatório
    ['apelido',   Str::random(101), 'Data too long for column'], // máximo 100 caracteres
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Andar::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['predio_id', 10,   'Cannot add or update a child row'], // não existente
    ['predio_id', null, 'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('cria muitos andares', function () {
    Andar::factory(30)->create();

    expect(Andar::count())->toBe(30);
});

test('andares com apelido null não são consideradas duplicadas', function () {
    $predio = Predio::factory()->create();

    Andar::factory()->for($predio)->create(['apelido' => null]);
    Andar::factory()->for($predio)->create(['apelido' => null]);
    Andar::factory()->for($predio)->create(['apelido' => '10']);

    $predio->load(['andares' => function ($query) {
        $query->whereNull('apelido');
    }]);

    expect($predio->andares)->toHaveCount(2);
});

test('aceita campos em seus tamanhos mínimos', function () {
    Andar::factory()->create(['numero' => -2147483648]);

    expect(Andar::count())->toBe(1);
});

test('aceita campos em seus tamanhos máximos', function () {
    Andar::factory()->create([
        'numero' => 2147483647,
        'apelido' => Str::random(100),
        'descricao' => Str::random(255),
    ]);

    expect(Andar::count())->toBe(1);
});

test('zero é um valor válido para o número do andar', function () {
    Andar::factory()->create(['numero' => 0]);

    $andar = Andar::first();

    expect($andar->numero)->toBe(0);
});

test('campos opcionais estão definidos', function () {
    Andar::factory()->create([
        'apelido' => null,
        'descricao' => null,
    ]);

    expect(Andar::count())->toBe(1);
});

test('método criarSala salva a sala como como filha do andar e cria a estante e a prateleira padrão', function () {
    $sala = new Sala();
    $sala->numero = 10;
    $sala->descricao = 'foo';

    $andar = Andar::factory()->create();

    $andar->criarSala($sala);

    $andar->load('salas.estantes.prateleiras');

    $sala = $andar->salas->first();
    $estante = $sala->estantes->first();
    $prateleira = $estante->prateleiras->first();

    expect($sala->numero)->toBe('10')
    ->and($sala->descricao)->toBe('foo')
    ->and($sala->andar_id)->toBe($andar->id)
    ->and($estante->numero)->toBe(0)
    ->and($estante->apelido)->toBe(__('Não informada'))
    ->and($estante->descricao)->toBe(__('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado'))
    ->and($estante->sala_id)->toBe($sala->id)
    ->and($prateleira->numero)->toBe(0)
    ->and($prateleira->apelido)->toBe(__('Não informada'))
    ->and($prateleira->estante_id)->toBe($estante->id)
    ->and($prateleira->descricao)->toBe(__('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado'));
});

test('um andar pertence a um prédio', function () {
    $andar = Andar::factory()->for(Predio::factory(), 'predio')->create();

    $andar->load(['predio']);

    expect($andar->predio)->toBeInstanceOf(Predio::class);
});

test('um andar possui muitas salas', function () {
    Andar::factory()->has(Sala::factory(3), 'salas')->create();

    $andar = Andar::with('salas')->first();

    expect($andar->salas)->toHaveCount(3);
});

test('método linksPais retorna as rotas de edição ordenadas do pai mais distante para o mais próximo se root for false', function () {
    Andar::factory()->create();

    $andar = Andar::hierarquia()->first();

    expect($andar->linksPais(false)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $andar->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $andar->predio_id),
    ]);
});

test('método linksPais retorna as rotas de edição, inclusise do elemento root, ordenadas do pai mais distante para o mais próximo se root for true', function () {
    Andar::factory()->create();

    $andar = Andar::hierarquia()->first();

    expect($andar->linksPais(true)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $andar->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $andar->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $andar->id),
    ]);
});

test('método linksPais retorna os links baseados nos dados hierárquicos presentes no modelo ou, se não presentes, os busca no banco de dados', function () {
    Andar::factory()->create();

    $andar = Andar::first();
    $andar->load('predio');

    expect($andar->linksPais(false)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $andar->predio->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $andar->predio_id),
    ]);
});

test('método hierarquia retorna todos os andares com o id e nome do respectivo prédio e localidade e o respectivo número de salas', function () {
    Andar::factory()->create(['numero' => 10]);
    Andar::factory()->has(Sala::factory(1), 'salas')->create(['numero' => 20]);
    Andar::factory()->has(Sala::factory(2), 'salas')->create(['numero' => 30]);

    $andares = Andar::hierarquia()->get();

    $andar_10 = $andares->firstWhere('numero', 10);
    $andar_20 = $andares->firstWhere('numero', 20);
    $andar_30 = $andares->firstWhere('numero', 30);

    expect($andares)->toHaveCount(3)
    ->and(empty($andar_10->localidade_id))->toBeFalse()
    ->and(empty($andar_10->localidade_nome))->toBeFalse()
    ->and(empty($andar_10->predio_id))->toBeFalse()
    ->and(empty($andar_10->predio_nome))->toBeFalse()
    ->and($andar_10->salas_count)->toBe(0)
    ->and($andar_20->salas_count)->toBe(1)
    ->and($andar_30->salas_count)->toBe(2);
});

test('retorna os andares ordenados pelo escopo de ordenação padrão', function () {
    Andar::factory()->create(['numero' => 100]);
    Andar::factory()->create(['numero' => 1]);
    Andar::factory()->create(['numero' => 10]);

    $andares = Andar::ordenacaoPadrao()->get();

    expect($andares->get(0)->numero)->toBe(1)
    ->and($andares->get(1)->numero)->toBe(10)
    ->and($andares->get(2)->numero)->toBe(100);
});
