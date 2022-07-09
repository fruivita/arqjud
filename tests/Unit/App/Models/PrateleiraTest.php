<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Caixa;
use App\Models\Prateleira;
use App\Models\Estante;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar prateleiras duplicadas, isto é, com mesmo número/apelido e estante', function () {
    $estante = Estante::factory()->create();

    expect(
        fn () => Prateleira::factory(2)->create([
            'numero' => 100,
            'estante_id' => $estante->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Prateleira::factory(2)->create([
            'apelido' => '100',
            'estante_id' => $estante->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar prateleira com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Prateleira::factory()->create([$campo => $valor])
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
        fn () => Prateleira::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['estante_id', 10,   'Cannot add or update a child row'], // não existente
    ['estante_id', null, 'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('cria muitas prateleiras', function () {
    Prateleira::factory(30)->create();

    expect(Prateleira::count())->toBe(30);
});

test('prateleiras com apelido null não são consideradas duplicadas', function () {
    $estante = Estante::factory()->create();

    Prateleira::factory()->for($estante, 'estante')->create(['apelido' => null]);
    Prateleira::factory()->for($estante, 'estante')->create(['apelido' => null]);
    Prateleira::factory()->for($estante, 'estante')->create(['apelido' => '10']);

    $estante->load(['prateleiras' => function ($query) {
        $query->whereNull('apelido');
    }]);

    expect($estante->prateleiras)->toHaveCount(2);
});

test('aceita campos em seus tamanhos mínimos', function () {
    Prateleira::factory()->create(['numero' => 0]);

    expect(Prateleira::count())->toBe(1);
});

test('aceita campos em seus tamanhos máximos', function () {
    Prateleira::factory()->create([
        'numero' => 4294967295,
        'apelido' => Str::random(100),
        'descricao' => Str::random(255),
    ]);

    expect(Prateleira::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    Prateleira::factory()->create([
        'apelido' => null,
        'descricao' => null,
    ]);

    expect(Prateleira::count())->toBe(1);
});

test('zero é um valor válido para o número da prateleira', function () {
    Prateleira::factory()->create(['numero' => 0]);

    $prateleira = Prateleira::first();

    expect($prateleira->numero)->toBe(0);
});

test('método modeloPadrao retorna o modelo com os atributos esperados', function () {
    $prateleira = Prateleira::modeloPadrao();

    expect($prateleira->numero)->toBe(0)
    ->and($prateleira->apelido)->toBe(__('Não informada'))
    ->and($prateleira->descricao)->toBe(__('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado'));
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

test('método linksPais retorna as rotas de edição ordenadas do pai mais distante para o mais próximo se root for false', function () {
    Prateleira::factory()->create();

    $prateleira = Prateleira::hierarquia()->first();

    expect($prateleira->linksPais(false)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $prateleira->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $prateleira->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $prateleira->andar_id),
        __('Sala') => route('arquivamento.cadastro.sala.edit', $prateleira->sala_id),
        __('Estante') => route('arquivamento.cadastro.estante.edit', $prateleira->estante_id),
    ]);
});

test('método linksPais retorna as rotas de edição, inclusise do elemento root, ordenadas do pai mais distante para o mais próximo se root for true', function () {
    Prateleira::factory()->create();

    $prateleira = Prateleira::hierarquia()->first();

    expect($prateleira->linksPais(true)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $prateleira->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $prateleira->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $prateleira->andar_id),
        __('Sala') => route('arquivamento.cadastro.sala.edit', $prateleira->sala_id),
        __('Estante') => route('arquivamento.cadastro.estante.edit', $prateleira->estante_id),
        __('Prateleira') => route('arquivamento.cadastro.prateleira.edit', $prateleira->id),
    ]);
});

test('método linksPais retorna os links baseados nos dados hierárquicos presentes no modelo ou, se não presentes, os busca no banco de dados', function () {
    Prateleira::factory()->create();

    $prateleira = Prateleira::first();
    $prateleira->load('estante.sala.andar.predio');

    expect($prateleira->linksPais(true)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $prateleira->estante->sala->andar->predio->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $prateleira->estante->sala->andar->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $prateleira->estante->sala->andar_id),
        __('Sala') => route('arquivamento.cadastro.sala.edit', $prateleira->estante->sala->id),
        __('Estante') => route('arquivamento.cadastro.estante.edit', $prateleira->estante_id),
        __('Prateleira') => route('arquivamento.cadastro.prateleira.edit', $prateleira->id),
    ]);
});

test('método hierarquia retorna todos as prateleiras com o id e nome/número da respectiva estante, sala, andar, prédio e localidade e o respectivo número de caixas', function () {
    Prateleira::factory()->create(['numero' => 10]);
    Prateleira::factory()->has(Caixa::factory(1), 'caixas')->create(['numero' => 20]);
    Prateleira::factory()->has(Caixa::factory(2), 'caixas')->create(['numero' => 30]);

    $prateleiras = Prateleira::hierarquia()->get();

    $prateleira_10 = $prateleiras->firstWhere('numero', 10);
    $prateleira_20 = $prateleiras->firstWhere('numero', 20);
    $prateleira_30 = $prateleiras->firstWhere('numero', 30);

    expect($prateleiras)->toHaveCount(3)
    ->and(empty($prateleira_10->localidade_id))->toBeFalse()
    ->and(empty($prateleira_10->localidade_nome))->toBeFalse()
    ->and(empty($prateleira_10->predio_id))->toBeFalse()
    ->and(empty($prateleira_10->predio_nome))->toBeFalse()
    ->and(empty($prateleira_10->andar_id))->toBeFalse()
    ->and(empty($prateleira_10->andar_apelido))->toBeFalse()
    ->and(empty($prateleira_10->andar_numero))->toBeFalse()
    ->and(empty($prateleira_10->sala_id))->toBeFalse()
    ->and(empty($prateleira_10->sala_numero))->toBeFalse()
    ->and(empty($prateleira_10->estante_id))->toBeFalse()
    ->and(empty($prateleira_10->estante_apelido))->toBeFalse()
    ->and(empty($prateleira_10->estante_numero))->toBeFalse()
    ->and($prateleira_10->caixas_count)->toBe(0)
    ->and($prateleira_20->caixas_count)->toBe(1)
    ->and($prateleira_30->caixas_count)->toBe(2);
});

test('método paraHumano retorna os dados em formato para leitura humana', function () {
    $estante = Estante::factory()->create(['numero' => 10]);
    Prateleira::factory()->for($estante, 'estante')->create(['numero' => 100]);

    $prateleira = Prateleira::hierarquia()->first();

    expect($prateleira->para_humano)->toBe(100)
    ->and($prateleira->estante_para_humano)->toBe(10);
});

test('método paraHumano retorna "Não informada" se o número da prateleira for zero', function () {
    $estante = Estante::factory()->create(['numero' => 0]);
    Prateleira::factory()->for($estante, 'estante')->create(['numero' => 0]);

    $prateleira = Prateleira::hierarquia()->first();

    expect($prateleira->para_humano)->toBe(__('Não informada'))
    ->and($prateleira->estante_para_humano)->toBe(__('Não informada'));
});
