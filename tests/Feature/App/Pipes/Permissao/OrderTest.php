<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Permissao;
use App\Pipes\Permissao\Order;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo id asc', function (string $coluna, string $direcao) {
    Permissao::factory()->create(['id' => 2]);
    Permissao::factory()->create(['id' => 1]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $perfis = Pipeline::make()
        ->send(Permissao::query())
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($perfis->toArray())->toMatchArray([1, 2]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo nome', function () {
    Permissao::factory()->create(['id' => 1, 'nome' => 'bbb']);
    Permissao::factory()->create(['id' => 2, 'nome' => 'aaa']);

    request()->merge(['order' => ['nome' => 'desc']]);

    $perfis = Pipeline::make()
        ->send(Permissao::query())
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($perfis->toArray())->toMatchArray([1, 2]);
});

test('com todas as ordenações específicas na permissão', function () {
    request()->merge(['order' => [
        'nome' => 'asc',
        'slug' => 'asc',
    ]]);

    $query = Pipeline::make()
        ->send(Permissao::query())
        ->through([Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
