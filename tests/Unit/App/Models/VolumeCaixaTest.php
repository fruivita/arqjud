<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Prateleira;
use App\Models\VolumeCaixa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar volumes de caixa duplicados, isto é, com mesmo número/apelido e caixa', function () {
    $caixa = Caixa::factory()->create();

    expect(
        fn () => VolumeCaixa::factory(2)->create([
            'numero' => 10,
            'caixa_id' => $caixa->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => VolumeCaixa::factory(2)->create([
            'apelido' => 10,
            'caixa_id' => $caixa->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar volume de caixa com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => VolumeCaixa::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['numero',  'foo',            'Incorrect integer value'],  // não conversível em inteiro
    ['numero',  -1,               'Out of range value'],       // min 0
    ['numero',  4294967296,       'Out of range value'],       // max 4294967295
    ['apelido', Str::random(101), 'Data too long for column'], // máximo 100 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => VolumeCaixa::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['caixa_id', 10,   'Cannot add or update a child row'], // não existente
    ['caixa_id', null, 'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('cria muitos volumes de caixa', function () {
    VolumeCaixa::factory(30)->create();

    expect(VolumeCaixa::count())->toBe(30);
});

test('aceita campos em seus tamanhos máximos', function () {
    VolumeCaixa::factory()->create([
        'numero' => 4294967295,
        'apelido' => Str::random(100),
    ]);

    expect(VolumeCaixa::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    VolumeCaixa::factory()->create(['apelido' => null]);

    expect(VolumeCaixa::count())->toBe(1);
});

test('um volume de caixa pertente a uma caixa', function () {
    $volume_caixa = VolumeCaixa::factory()->for(Caixa::factory(), 'caixa')->create();

    $volume_caixa->load(['caixa']);

    expect($volume_caixa->caixa)->toBeInstanceOf(Caixa::class);
});

test('método hierarquia retorna todos os volumes de caixa com o id e nome/número da respectiva caixa, prateleira, estante, sala, andar, prédio e localidade', function () {
    VolumeCaixa::factory(3)->create();

    $volumes_caixa = VolumeCaixa::hierarquia()->get();

    $volume_caixa = $volumes_caixa->random();

    expect($volumes_caixa)->toHaveCount(3)
    ->and(empty($volume_caixa->localidade_id))->toBeFalse()
    ->and(empty($volume_caixa->localidade_nome))->toBeFalse()
    ->and(empty($volume_caixa->predio_id))->toBeFalse()
    ->and(empty($volume_caixa->predio_nome))->toBeFalse()
    ->and(empty($volume_caixa->andar_id))->toBeFalse()
    ->and(empty($volume_caixa->andar_apelido))->toBeFalse()
    ->and(empty($volume_caixa->andar_numero))->toBeFalse()
    ->and(empty($volume_caixa->sala_id))->toBeFalse()
    ->and(empty($volume_caixa->sala_numero))->toBeFalse()
    ->and(empty($volume_caixa->estante_id))->toBeFalse()
    ->and(empty($volume_caixa->estante_numero))->toBeFalse()
    ->and(empty($volume_caixa->prateleira_id))->toBeFalse()
    ->and(empty($volume_caixa->prateleira_numero))->toBeFalse()
    ->and(empty($volume_caixa->caixa_id))->toBeFalse()
    ->and(empty($volume_caixa->caixa_numero))->toBeFalse()
    ->and(empty($volume_caixa->caixa_ano))->toBeFalse();
});

test('método paraHumano retorna os dados em formato para leitura humana', function () {
    $estante = Estante::factory()->create(['numero' => 10]);
    $prateleira = Prateleira::factory()->for($estante, 'estante')->create(['numero' => 100]);
    $caixa = Caixa::factory()->for($prateleira, 'prateleira')->create(['numero' => 1000, 'ano' => 2020]);
    VolumeCaixa::factory()->for($caixa, 'caixa')->create(['numero' => 50]);

    $volume_caixa = VolumeCaixa::hierarquia()->first();

    expect($volume_caixa->para_humano)->toBe('Vol. 50')
    ->and($volume_caixa->estante_para_humano)->toBe(10)
    ->and($volume_caixa->prateleira_para_humano)->toBe(100)
    ->and($volume_caixa->caixa_para_humano)->toBe('1000/2020');
});

test('método paraHumano retorna "Não informada" se o número da estante ou da prateleira for zero', function () {
    $estante = Estante::factory()->create(['numero' => 0]);
    $prateleira = Prateleira::factory()->for($estante, 'estante')->create(['numero' => 0]);
    $caixa = Caixa::factory()->for($prateleira, 'prateleira')->create();
    VolumeCaixa::factory()->for($caixa, 'caixa')->create();

    $volume_caixa = VolumeCaixa::hierarquia()->first();

    expect($volume_caixa->estante_para_humano)->toBe(__('Não informada'))
    ->and($volume_caixa->prateleira_para_humano)->toBe(__('Não informada'));
});

test('método gerar gera a quantidade de volumes informados', function () {
    $volumes = VolumeCaixa::gerar(5);

    $primeiro = $volumes->first();
    $ultimo = $volumes->last();

    expect($volumes)->toHaveCount(5)
    ->and($primeiro->numero)->toBe(1)
    ->and($primeiro->apelido)->toBe('Vol. 1')
    ->and($ultimo->numero)->toBe(5)
    ->and($ultimo->apelido)->toBe('Vol. 5');
});

test('método gerar aceita, opcionalmente, o número do primeiro volume', function () {
    $volumes = VolumeCaixa::gerar(5, 10);

    $primeiro = $volumes->first();
    $ultimo = $volumes->last();

    expect($volumes)->toHaveCount(5)
    ->and($primeiro->numero)->toBe(10)
    ->and($primeiro->apelido)->toBe('Vol. 10')
    ->and($ultimo->numero)->toBe(14)
    ->and($ultimo->apelido)->toBe('Vol. 14');
});

test('retorna os volumes de determinada caixa pelo escopo', function () {
    $caixa = Caixa::factory()->create();
    $volume_1 = VolumeCaixa::factory()->for($caixa, 'caixa')->create();
    $volume_2 = VolumeCaixa::factory()->for($caixa, 'caixa')->create();
    $volume_3 = VolumeCaixa::factory()->create();

    $volumes = VolumeCaixa::daCaixa($caixa->id)->pluck('id');

    expect($volumes)->toHaveCount(2)
    ->and($volumes)->toContain($volume_1->id)
    ->and($volumes)->toContain($volume_2->id)
    ->and($volumes)->not->toContain($volume_3->id);
});
