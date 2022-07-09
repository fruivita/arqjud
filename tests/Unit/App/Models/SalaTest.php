<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Andar;
use App\Models\Sala;
use App\Models\Estante;
use Illuminate\Database\QueryException;
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

test('lança exception ao tentar criar sala com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Sala::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',    null,             'cannot be null'],           // obrigatório
    ['numero',    Str::random(51),  'Data too long for column'], // máximo 50 caracteres
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Sala::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['andar_id', 10,   'Cannot add or update a child row'], // não existente
    ['andar_id', null, 'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('cria muitas salas', function () {
    Sala::factory(30)->create();

    expect(Sala::count())->toBe(30);
});

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

test('método criarEstante salva a estante como filha da sala e cria a prateleira padrão', function () {
    $estante = new Estante();
    $estante->numero = 10;
    $estante->descricao = 'foo';

    $sala = Sala::factory()->create();

    $sala->criarEstante($estante);

    $sala->load('estantes.prateleiras');

    $estante = $sala->estantes->first();
    $prateleira = $estante->prateleiras->first();

    expect($estante->numero)->toBe(10)
    ->and($estante->descricao)->toBe('foo')
    ->and($estante->sala_id)->toBe($sala->id)
    ->and($prateleira->numero)->toBe(0)
    ->and($prateleira->apelido)->toBe(__('Não informada'))
    ->and($prateleira->estante_id)->toBe($estante->id)
    ->and($prateleira->descricao)->toBe(__('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado'));
});

test('uma sala pertence a um andar', function () {
    $sala = Sala::factory()->for(Andar::factory(), 'andar')->create();

    $sala->load(['andar']);

    expect($sala->andar)->toBeInstanceOf(Andar::class);
});

test('uma sala possui muitas estantes', function () {
    Sala::factory()->has(Estante::factory(3), 'estantes')->create();

    $sala = Sala::with('estantes')->first();

    expect($sala->estantes)->toHaveCount(3);
});

test('método linksPais retorna as rotas de edição ordenadas do pai mais distante para o mais próximo se root for false', function () {
    Sala::factory()->create();

    $sala = Sala::hierarquia()->first();

    expect($sala->linksPais(false)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $sala->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $sala->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $sala->andar_id),
    ]);
});

test('método linksPais retorna as rotas de edição, inclusise do elemento root, ordenadas do pai mais distante para o mais próximo se root for true', function () {
    Sala::factory()->create();

    $sala = Sala::hierarquia()->first();

    expect($sala->linksPais(true)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $sala->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $sala->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $sala->andar_id),
        __('Sala') => route('arquivamento.cadastro.sala.edit', $sala->id),
    ]);
});

test('método linksPais retorna os links baseados nos dados hierárquicos presentes no modelo ou, se não presentes, os busca no banco de dados', function () {
    Sala::factory()->create();

    $sala = Sala::first();
    $sala->load('andar.predio');

    expect($sala->linksPais(true)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $sala->andar->predio->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $sala->andar->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $sala->andar_id),
        __('Sala') => route('arquivamento.cadastro.sala.edit', $sala->id),
    ]);
});

test('método hierarquia retorna todos as salas com o id e nome/número do respectivo andar, prédio e localidade e o respectivo número de estantes', function () {
    Sala::factory()->create(['numero' => 10]);
    Sala::factory()->has(Estante::factory(1), 'estantes')->create(['numero' => 20]);
    Sala::factory()->has(Estante::factory(2), 'estantes')->create(['numero' => 30]);

    $salas = Sala::hierarquia()->get();

    $sala_10 = $salas->firstWhere('numero', 10);
    $sala_20 = $salas->firstWhere('numero', 20);
    $sala_30 = $salas->firstWhere('numero', 30);

    expect($salas)->toHaveCount(3)
    ->and(empty($sala_10->localidade_id))->toBeFalse()
    ->and(empty($sala_10->localidade_nome))->toBeFalse()
    ->and(empty($sala_10->predio_id))->toBeFalse()
    ->and(empty($sala_10->predio_nome))->toBeFalse()
    ->and(empty($sala_10->andar_id))->toBeFalse()
    ->and(empty($sala_10->andar_apelido))->toBeFalse()
    ->and(empty($sala_10->andar_numero))->toBeFalse()
    ->and($sala_10->estantes_count)->toBe(0)
    ->and($sala_20->estantes_count)->toBe(1)
    ->and($sala_30->estantes_count)->toBe(2);
});
