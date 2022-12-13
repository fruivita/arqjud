<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Localidade;
use App\Pipes\Localidade\Order;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    Localidade::factory()->create(['id' => 1]);
    Localidade::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $localidades = Pipeline::make()
        ->send(Localidade::query())
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($localidades->toArray())->toBe([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo nome', function () {
    Localidade::factory()->create(['id' => 1, 'nome' => 'bbb']);
    Localidade::factory()->create(['id' => 2, 'nome' => 'aaa']);

    request()->merge(['order' => ['nome' => 'desc']]);

    $localidades = Pipeline::make()
        ->send(Localidade::query())
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($localidades->toArray())->toBe([1, 2]);
});

test('ordena pela quantidade de prédios filhos', function () {
    Localidade::factory()->hasPredios(1)->create(['id' => 1]);
    Localidade::factory()->hasPredios(2)->create(['id' => 2]);

    request()->merge(['order' => ['predios_count' => 'asc']]);

    $localidades = Pipeline::make()
        ->send(Localidade::query()->withCount('predios'))
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($localidades->toArray())->toBe([1, 2]);
});

test('com todas as ordenações específicas na localidade', function () {
    request()->merge(['order' => ['predios_count' => 'desc', 'nome' => 'asc', 'caixas_criadas_count' => 'asc']]);

    $query = Pipeline::make()
        ->send(Localidade::query())
        ->through([Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
