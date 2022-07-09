<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar prédios duplicados, isto é, com mesmo nome e localidade', function () {
    $localidade = Localidade::factory()->create();

    expect(
        fn () => Predio::factory(2)->create([
            'nome' => 'foo',
            'localidade_id' => $localidade->id,
        ])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar prédio com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Predio::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['nome',      Str::random(101), 'Data too long for column'], // máximo 100 caracteres
    ['nome',      null,             'cannot be null'],           // obrigatório
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Predio::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['localidade_id', 10,   'Cannot add or update a child row'], // não existente
    ['localidade_id', null, 'cannot be null'],                   // obrigatório
]);

// Caminho feliz
test('cria muitos prédios', function () {
    Predio::factory(30)->create();

    expect(Predio::count())->toBe(30);
});

test('aceita campos em seus tamanhos máximos', function () {
    Predio::factory()->create([
        'nome' => Str::random(100),
        'descricao' => Str::random(255),
    ]);

    expect(Predio::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    Localidade::factory()->create(['descricao' => null]);

    expect(Localidade::count())->toBe(1);
});

test('um prédio pertence a uma localidade', function () {
    $predio = Predio::factory()->for(Localidade::factory(), 'localidade')->create();

    $predio->load(['localidade']);

    expect($predio->localidade)->toBeInstanceOf(Localidade::class);
});

test('um prédio possui muitos andares', function () {
    Predio::factory()->has(Andar::factory(3), 'andares')->create();

    $predio = Predio::with('andares')->first();

    expect($predio->andares)->toHaveCount(3);
});

test('método linksPais retorna as rotas de edição ordenadas do pai mais distante para o mais próximo se root for false', function () {
    Predio::factory()->create();

    $predio = Predio::hierarquia()->first();

    expect($predio->linksPais(false)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $predio->localidade_id),
    ]);
});

test('método linksPais retorna as rotas de edição, inclusise do elemento root, ordenadas do pai mais distante para o mais próximo se root for true', function () {
    Predio::factory()->create();

    $predio = Predio::hierarquia()->first();

    expect($predio->linksPais(true)->toArray())->toBe([
        __('Localidade') => route('arquivamento.cadastro.localidade.edit', $predio->localidade_id),
        __('Prédio') => route('arquivamento.cadastro.predio.edit', $predio->id),
    ]);
});

test('método hierarquia retorna todos os prédios com o id e nome da respectiva localidade e o respectivo número de andares', function () {
    Predio::factory()->create(['nome' => 'foo']);
    Predio::factory()->has(Andar::factory(1), 'andares')->create(['nome' => 'bar']);
    Predio::factory()->has(Andar::factory(2), 'andares')->create(['nome' => 'baz']);

    $predios = Predio::hierarquia()->get();

    $predio_foo = $predios->firstWhere('nome', 'foo');
    $predio_bar = $predios->firstWhere('nome', 'bar');
    $predio_baz = $predios->firstWhere('nome', 'baz');

    expect($predios)->toHaveCount(3)
    ->and(empty($predio_foo->localidade_id))->toBeFalse()
    ->and(empty($predio_foo->localidade_nome))->toBeFalse()
    ->and($predio_foo->andares_count)->toBe(0)
    ->and($predio_bar->andares_count)->toBe(1)
    ->and($predio_baz->andares_count)->toBe(2);
});
