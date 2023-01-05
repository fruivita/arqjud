<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Perfil;
use App\Pipes\Perfil\Order;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo Poder desc', function (string $coluna, string $direcao) {
    Perfil::factory()->create(['id' => 1, 'poder' => 100]);
    Perfil::factory()->create(['id' => 2, 'poder' => 200]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $perfis = Pipeline::make()
        ->send(Perfil::query())
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($perfis->toArray())->toMatchArray([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo nome', function () {
    Perfil::factory()->create(['id' => 1, 'nome' => 'bbb']);
    Perfil::factory()->create(['id' => 2, 'nome' => 'aaa']);

    request()->merge(['order' => ['nome' => 'desc']]);

    $perfis = Pipeline::make()
        ->send(Perfil::query())
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($perfis->toArray())->toMatchArray([1, 2]);
});

test('ordena pela quantidade de usuários filhos', function () {
    Perfil::factory()->hasUsuarios(1)->create(['id' => 1]);
    Perfil::factory()->hasUsuarios(2)->create(['id' => 2]);

    request()->merge(['order' => ['usuarios_count' => 'asc']]);

    $perfis = Pipeline::make()
        ->send(Perfil::query()->withCount('usuarios'))
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($perfis->toArray())->toMatchArray([1, 2]);
});

test('com todas as ordenações específicas no perfil', function () {
    request()->merge(['order' => [
        'nome' => 'asc',
        'slug' => 'asc',
        'poder' => 'asc',
        'usuarios_count' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(Perfil::query())
        ->through([Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
