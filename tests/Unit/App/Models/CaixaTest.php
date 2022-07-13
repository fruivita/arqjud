<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\VolumeCaixa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar caixas duplicados, isto é, com mesmo ano, número, se é guarda permanente, local de criação e complemento', function () {
    $localidade = Localidade::factory()->create();

    expect(
        fn () => Caixa::factory(2)->create([
            'numero' => 100,
            'ano' => 2020,
            'guarda_permanente' => true,
            'complemento' => 'foo',
            'localidade_criadora_id' => $localidade->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar caixa com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Caixa::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',      -1,         'Out of range'],                   // min 0
    ['numero',      4294967296, 'Out of range'],                   // max 4294967295
    ['numero',      'foo',      'Incorrect integer value'],        // não conversível em inteiro
    ['ano',         -1,         'Out of range value'],             // min 0
    ['ano',         65536,      'Out of range value'],             // max 65536
    ['ano',         'foo',      'Incorrect integer value'],        // não conversível em inteiro
    ['complemento', Str::random(51), 'Data too long for column'],  // máximo 50 caracteres
    ['descricao',   Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Caixa::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['prateleira_id',          10,   'Cannot add or update a child row'], // não existente
    ['prateleira_id',          null, 'cannot be null'],                   // obrigatório
    ['localidade_criadora_id', 10,   'Cannot add or update a child row'], // não existente
    ['localidade_criadora_id', null, 'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('cria muitas caixas', function () {
    Caixa::factory(30)->create();

    expect(Caixa::count())->toBe(30);
});

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

test('uma caixa possui muitos volumes de caixa', function () {
    Caixa::factory()->has(VolumeCaixa::factory(3), 'volumes')->create();

    $caixa = Caixa::with('volumes')->first();

    expect($caixa->volumes)->toHaveCount(3);
});

test('método proximoNumeroCaixa retorna o número da próxima caixa a ser criada', function () {
    $localidade_a = Localidade::factory()->create();
    $localidade_b = Localidade::factory()->create();

    Caixa::factory()->for($localidade_a, 'localidadeCriadora')->create([
        'ano' => 2020,
        'numero' => 20,
        'guarda_permanente' => true,
        'complemento' => 'foo',
        'localidade_criadora_id' => $localidade_a->id,
    ]);

    Caixa::factory()->for($localidade_b, 'localidadeCriadora')->create([
        'ano' => 2020,
        'numero' => 30,
        'guarda_permanente' => true,
        'complemento' => null,
        'localidade_criadora_id' => $localidade_b->id,
    ]);

    expect(Caixa::proximoNumeroCaixa(2020, true, $localidade_a->id, 'foo'))->toBe(21) // criada na localidade b
    ->and(Caixa::proximoNumeroCaixa(2020, true, $localidade_a->id))->toBe(1)          // sem complemento
    ->and(Caixa::proximoNumeroCaixa(2020, true, $localidade_a->id, 'bar'))->toBe(1)   // outro complemento
    ->and(Caixa::proximoNumeroCaixa(2020, false, $localidade_a->id, 'foo'))->toBe(1)  // não é guarda permanente
    ->and(Caixa::proximoNumeroCaixa(2021, true, $localidade_a->id, 'foo'))->toBe(1)   // outro ano
    ->and(Caixa::proximoNumeroCaixa(2020, true, $localidade_b->id))->toBe(31)         // criada na localidade b
    ->and(Caixa::proximoNumeroCaixa(2020, true, $localidade_b->id, ''))->toBe(31)     // criada na localidade b
    ->and(Caixa::proximoNumeroCaixa(2020, true, $localidade_b->id, 'bar'))->toBe(1)   // outro complemento
    ->and(Caixa::proximoNumeroCaixa(2020, false, $localidade_b->id, 'foo'))->toBe(1)  // não é guarda permanente
    ->and(Caixa::proximoNumeroCaixa(2021, true, $localidade_b->id, 'foo'))->toBe(1);  // outro ano
});

test('método proximoNumeroVolume retorna o número do próximo volume da caixa a ser criado', function () {
    $caixa = Caixa::factory()
        ->has(VolumeCaixa::factory()->state(['numero' => 10]), 'volumes')
        ->create();

    expect($caixa->proximoNumeroVolume())->toBe(11);
});

test('método criarVolume cria um volume para a caixa', function () {
    $caixa = Caixa::factory()->create();

    $volume = $caixa->criarVolume(11);

    expect($volume->numero)->toBe(11)
    ->and($volume->apelido)->toBe('Vol. 11');
});

test('método linksPais retorna as rotas de edição ordenadas do pai mais distante para o mais próximo se root for false', function () {
    Caixa::factory()->create();

    $caixa = Caixa::hierarquia()->first();

    expect($caixa->linksPais(false)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $caixa->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $caixa->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $caixa->andar_id),
        __('Sala') => route('arquivamento.cadastro.sala.edit', $caixa->sala_id),
        __('Estante') => route('arquivamento.cadastro.estante.edit', $caixa->estante_id),
        __('Prateleira') => route('arquivamento.cadastro.prateleira.edit', $caixa->prateleira_id),
    ]);
});

test('método linksPais retorna as rotas de edição, inclusise do elemento root, ordenadas do pai mais distante para o mais próximo se root for true', function () {
    Caixa::factory()->create();

    $caixa = Caixa::hierarquia()->first();

    expect($caixa->linksPais(true)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $caixa->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $caixa->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $caixa->andar_id),
        __('Sala') => route('arquivamento.cadastro.sala.edit', $caixa->sala_id),
        __('Estante') => route('arquivamento.cadastro.estante.edit', $caixa->estante_id),
        __('Prateleira') => route('arquivamento.cadastro.prateleira.edit', $caixa->prateleira_id),
        __('Caixa') => route('arquivamento.cadastro.caixa.edit', $caixa->id),
    ]);
});

test('método linksPais retorna os links baseados nos dados hierárquicos presentes no modelo ou, se não presentes, os busca no banco de dados', function () {
    Caixa::factory()->create();

    $caixa = Caixa::first();
    $caixa->load('prateleira.estante.sala.andar.predio');

    expect($caixa->linksPais(true)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $caixa->prateleira->estante->sala->andar->predio->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $caixa->prateleira->estante->sala->andar->predio_id),
        __('Andar') => route('arquivamento.cadastro.andar.edit', $caixa->prateleira->estante->sala->andar_id),
        __('Sala') => route('arquivamento.cadastro.sala.edit', $caixa->prateleira->estante->sala->id),
        __('Estante') => route('arquivamento.cadastro.estante.edit', $caixa->prateleira->estante_id),
        __('Prateleira') => route('arquivamento.cadastro.prateleira.edit', $caixa->prateleira->id),
        __('Caixa') => route('arquivamento.cadastro.caixa.edit', $caixa->id),
    ]);
});

test('método criarMuitas cria e salva caixas com números sequenciais e com os demais atributos idênticos', function () {
    $template = Caixa::factory()->makeOne(['numero' => 10]);
    $prateleira = Prateleira::factory()->create();

    Caixa::criarMuitas($template, 30, 5, $prateleira);

    $caixas = Caixa::with('volumes')->get();

    $caixa = $caixas->random();

    expect($caixas)->toHaveCount(30)
    ->and($prateleira->load('caixas')->caixas)->toHaveCount(30)
    ->and($caixas->first()->numero)->toBe(10)
    ->and($caixas->last()->numero)->toBe(39)
    ->and($caixa->ano)->toBe($template->ano)
    ->and($caixa->guarda_permanente)->toBe($template->guarda_permanente)
    ->and($caixa->complemento)->toBe($template->complemento)
    ->and($caixa->descricao)->toBe($template->descricao)
    ->and($caixa->localidade_criadora_id)->toBe($template->localidade_criadora_id)
    ->and($caixa->volumes)->toHaveCount(5)
    ->and($caixa->volumes->first()->numero)->toBe(1)
    ->and($caixa->volumes->last()->numero)->toBe(5);
});

test('método hierarquia retorna todos as caixas com o id e nome/número da respectiva localidade criadora, prateleira, estante, sala, andar, prédio e localidade e o respectivo número de volumes', function () {
    Caixa::factory()->create(['numero' => 10]);
    Caixa::factory()->has(VolumeCaixa::factory(1), 'volumes')->create(['numero' => 20]);
    Caixa::factory()->has(VolumeCaixa::factory(2), 'volumes')->create(['numero' => 30]);

    $caixas = Caixa::hierarquia()->get();

    $caixa_10 = $caixas->firstWhere('numero', 10);
    $caixa_20 = $caixas->firstWhere('numero', 20);
    $caixa_30 = $caixas->firstWhere('numero', 30);

    expect($caixas)->toHaveCount(3)
    ->and(empty($caixa_10->localidade_id))->toBeFalse()
    ->and(empty($caixa_10->localidade_nome))->toBeFalse()
    ->and(empty($caixa_10->predio_id))->toBeFalse()
    ->and(empty($caixa_10->predio_nome))->toBeFalse()
    ->and(empty($caixa_10->andar_id))->toBeFalse()
    ->and(empty($caixa_10->andar_apelido))->toBeFalse()
    ->and(empty($caixa_10->andar_numero))->toBeFalse()
    ->and(empty($caixa_10->sala_id))->toBeFalse()
    ->and(empty($caixa_10->sala_numero))->toBeFalse()
    ->and(empty($caixa_10->estante_id))->toBeFalse()
    ->and(empty($caixa_10->estante_numero))->toBeFalse()
    ->and(empty($caixa_10->prateleira_id))->toBeFalse()
    ->and(empty($caixa_10->prateleira_numero))->toBeFalse()
    ->and(empty($caixa_10->localidade_criadora_id))->toBeFalse()
    ->and(empty($caixa_10->localidade_criadora_nome))->toBeFalse()
    ->and($caixa_10->volumes_count)->toBe(0)
    ->and($caixa_20->volumes_count)->toBe(1)
    ->and($caixa_30->volumes_count)->toBe(2);
});

test('método paraHumano retorna o os dados em formato para leitura humana', function () {
    $estante = Estante::factory()->create(['numero' => 10]);
    $prateleira = Prateleira::factory()->for($estante, 'estante')->create(['numero' => 100]);
    Caixa::factory()->for($prateleira, 'prateleira')->create(['numero' => 1000, 'ano' => 2020]);

    $caixa = Caixa::hierarquia()->first();

    expect($caixa->para_humano)->toBe('1000/2020')
    ->and($caixa->estante_para_humano)->toBe(10)
    ->and($caixa->prateleira_para_humano)->toBe(100);
});

test('método paraHumano retorna "Não informada" se o número da estante ou da prateleira for zero', function () {
    $estante = Estante::factory()->create(['numero' => 0]);
    $prateleira = Prateleira::factory()->for($estante, 'estante')->create(['numero' => 0]);
    Caixa::factory()->for($prateleira, 'prateleira')->create();

    $caixa = Caixa::hierarquia()->first();

    expect($caixa->estante_para_humano)->toBe(__('Não informada'))
    ->and($caixa->prateleira_para_humano)->toBe(__('Não informada'));
});

test('retorna as caixas de determinada prateleira pelo escopo', function () {
    $prateleira = Prateleira::factory()->create();
    $caixa_1 = Caixa::factory()->for($prateleira, 'prateleira')->create();
    $caixa_2 = Caixa::factory()->for($prateleira, 'prateleira')->create();
    $caixa_3 = Caixa::factory()->create();

    $caixas = Caixa::daPrateleira($prateleira->id)->pluck('id');

    expect($caixas)->toHaveCount(2)
    ->and($caixas)->toContain($caixa_1->id)
    ->and($caixas)->toContain($caixa_2->id)
    ->and($caixas)->not->toContain($caixa_3->id);
});
