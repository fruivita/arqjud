<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Predio\JoinLocalidade;
use App\Filters\Predio\Order;
use App\Models\Predio;
use MichaelRubel\EnhancedPipeline\Pipeline;
use Mockery\MockInterface;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    Predio::factory()->create(['id' => 1]);
    Predio::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $predios = Pipeline::make()
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
    Predio::factory()->create(['id' => 1, 'nome' => 'bbb']);
    Predio::factory()->create(['id' => 2, 'nome' => 'aaa']);

    request()->merge(['order' => ['nome' => 'desc']]);

    $predios = Pipeline::make()
        ->send(Predio::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($predios->toArray())->toBe([1, 2]);
});

test('todos os métodos de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive(str()->camel($campo))
        ->withSomeOfArgs('desc')
        ->once();

    request()->merge(['order' => [$campo => 'desc']]);

    Pipeline::make()
        ->send(Predio::query())
        ->through([Order::class])
        ->thenReturn();
})->with([
    'nome',
    'localidade_pai_nome',
    'andares_count',
]);

test('todas as ordenações possíveis no request do prédio', function () {
    request()->merge(['order' => [
        'nome' => 'asc',
        'localidade_pai_nome' => 'asc',
        'andares_count' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(Predio::query())
        ->through([JoinLocalidade::class, Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
