<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Guia;
use App\Pipes\Guia\Order;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    Guia::factory()->create(['id' => 1]);
    Guia::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $guias = Pipeline::make()
        ->send(Guia::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($guias->toArray())->toMatchArray([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo número', function () {
    Guia::factory()->create(['id' => 1, 'numero' => 20]);
    Guia::factory()->create(['id' => 2, 'numero' => 10]);

    request()->merge(['order' => ['numero' => 'desc']]);

    $guias = Pipeline::make()
        ->send(Guia::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($guias->toArray())->toMatchArray([1, 2]);
});

test('todos os métodos de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive(str()->camel($campo))
        ->withSomeOfArgs('desc')
        ->once();

    request()->merge(['order' => [$campo => 'desc']]);

    Pipeline::make()
        ->send(Guia::query())
        ->through([Order::class])
        ->thenReturn();
})->with([
    'numero',
    'ano',
    'gerada_em',
]);

test('todas as ordenações possíveis no request da guia', function () {
    request()->merge(['order' => [
        'numero' => 'asc',
        'ano' => 'asc',
        'gerada_em' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(Guia::query())
        ->through([Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
