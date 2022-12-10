<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Estante\JoinLocalidade;
use App\Filters\Estante\Order;
use App\Models\Estante;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    Estante::factory()->create(['id' => 1]);
    Estante::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $estantes = Pipeline::make()
        ->send(Estante::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($estantes->toArray())->toBe([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo número', function () {
    Estante::factory()->create(['id' => 1, 'numero' => '20']);
    Estante::factory()->create(['id' => 2, 'numero' => '10']);

    request()->merge(['order' => ['numero' => 'desc']]);

    $estantes = Pipeline::make()
        ->send(Estante::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($estantes->toArray())->toBe([1, 2]);
});

test('todos os métodos de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive(str()->camel($campo))
        ->withSomeOfArgs('desc')
        ->once();

    request()->merge(['order' => [$campo => 'desc']]);

    Pipeline::make()
        ->send(Estante::query())
        ->through([Order::class])
        ->thenReturn();
})->with([
    'numero',
    'localidade_pai_nome',
    'predio_pai_nome',
    'andar_pai_numero',
    'andar_pai_apelido',
    'sala_pai_numero',
    'prateleiras_count',
]);

test('todas as ordenações possíveis no request da estante', function () {
    request()->merge(['order' => [
        'numero' => 'asc',
        'localidade_pai_nome' => 'asc',
        'predio_pai_nome' => 'asc',
        'andar_pai_numero' => 'asc',
        'andar_pai_apelido' => 'desc',
        'sala_pai_numero' => 'desc',
        'prateleiras_count' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(Estante::query())
        ->through([JoinLocalidade::class, Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
