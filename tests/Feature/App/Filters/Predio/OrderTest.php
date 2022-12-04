<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Predio\JoinLocalidade;
use App\Filters\Predio\Order;
use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Pipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    Predio::factory()->create(['id' => 1]);
    Predio::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $predios = app(Pipeline::class)
        ->send(Predio::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($predios->toArray())->toBe([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo nome', function () {
    Predio::factory()->create(['id' => 1, 'nome' => 'aaa']);
    Predio::factory()->create(['id' => 2, 'nome' => 'bbb']);

    request()->merge(['order' => ['nome' => 'desc']]);

    $predios = app(Pipeline::class)
        ->send(Predio::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($predios->toArray())->toBe([2, 1]);
});

test('ordena pela nome da localidade pai', function () {
    Predio::factory()->has(Localidade::factory(1, ['nome' => 'aaa']), 'localidade')->create(['id' => 1]);
    Predio::factory()->has(Localidade::factory(1, ['nome' => 'bbb']), 'localidade')->create(['id' => 2]);

    request()->merge(['order' => ['localidade_pai_nome' => 'desc']]);

    $predios = app(Pipeline::class)
        ->send(Predio::query())
        ->through([JoinLocalidade::class, Order::class])
        ->thenReturn()
        ->pluck('predios.id');

    expect($predios->toArray())->toBe([2, 1]);
});

test('ordena pela quantidade de andares filhos', function () {
    Predio::factory()->has(Andar::factory(2), 'andares')->create(['id' => 1]);
    Predio::factory()->has(Andar::factory(1), 'andares')->create(['id' => 2]);

    request()->merge(['order' => ['andares_count' => 'asc']]);

    $predios = app(Pipeline::class)
        ->send(Predio::query()->withCount('andares'))
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($predios->toArray())->toBe([2, 1]);
});


test('com todas as ordenações específicas no prédio', function () {
    request()->merge(['order' => ['andares_count' => 'desc', 'nome' => 'asc', 'localidade_pai_nome' => 'asc']]);

    $query = app(Pipeline::class)
        ->send(Predio::query())
        ->through([JoinLocalidade::class, Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
