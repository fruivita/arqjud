<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\TipoProcesso;
use App\Pipes\TipoProcesso\Order;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    TipoProcesso::factory()->create(['id' => 1]);
    TipoProcesso::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $tipos_processo = Pipeline::make()
        ->send(TipoProcesso::query())
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($tipos_processo->toArray())->toMatchArray([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo nome', function () {
    TipoProcesso::factory()->create(['id' => 1, 'nome' => 'bbb']);
    TipoProcesso::factory()->create(['id' => 2, 'nome' => 'aaa']);

    request()->merge(['order' => ['nome' => 'desc']]);

    $tipos_processo = Pipeline::make()
        ->send(TipoProcesso::query())
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($tipos_processo->toArray())->toMatchArray([1, 2]);
});

test('ordena pela quantidade de caixas filhas', function () {
    TipoProcesso::factory()->hasCaixas(1)->create(['id' => 1]);
    TipoProcesso::factory()->hasCaixas(2)->create(['id' => 2]);

    request()->merge(['order' => ['caixas_count' => 'asc']]);

    $tipos_processo = Pipeline::make()
        ->send(TipoProcesso::query()->withCount('caixas'))
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($tipos_processo->toArray())->toMatchArray([1, 2]);
});

test('com todas as ordenações específicas no tipo de processo', function () {
    request()->merge(['order' => ['caixas_count' => 'desc', 'nome' => 'asc']]);

    $query = Pipeline::make()
        ->send(TipoProcesso::query())
        ->through([Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
