<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Localidade\Order;
use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Pipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    Localidade::factory()->create(['id' => 1]);
    Localidade::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $localidades = app(Pipeline::class)
        ->send(Localidade::query())
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($localidades->toArray())->toBe([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo nome', function () {
    Localidade::factory()->create(['id' => 1, 'nome' => 'aaa']);
    Localidade::factory()->create(['id' => 2, 'nome' => 'bbb']);

    request()->merge(['order' => ['nome' => 'desc']]);

    $localidades = app(Pipeline::class)
        ->send(Localidade::query())
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($localidades->toArray())->toBe([2, 1]);
});

test('ordena pela quantidade de prédios filhos', function () {
    Localidade::factory()->has(Predio::factory(2), 'predios')->create(['id' => 1]);
    Localidade::factory()->has(Predio::factory(1), 'predios')->create(['id' => 2]);

    request()->merge(['order' => ['predios_count' => 'asc']]);

    $localidades = app(Pipeline::class)
        ->send(Localidade::query()->withCount('predios'))
        ->through([Order::class])
        ->thenReturn()->pluck('id');

    expect($localidades->toArray())->toBe([2, 1]);
});

test('com todas as ordenações específicas na localidade', function () {
    request()->merge(['order' => ['predios_count' => 'desc', 'nome' => 'asc', 'caixas_criadas_count' => 'asc']]);

    $query = app(Pipeline::class)
        ->send(Localidade::query())
        ->through([Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
