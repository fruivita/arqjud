<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Sala;
use App\Pipes\Sala\JoinLocalidade;
use App\Pipes\Sala\Order;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    Sala::factory()->create(['id' => 1]);
    Sala::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $salas = Pipeline::make()
        ->send(Sala::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($salas->toArray())->toBe([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo número', function () {
    Sala::factory()->create(['id' => 1, 'numero' => '20']);
    Sala::factory()->create(['id' => 2, 'numero' => '10']);

    request()->merge(['order' => ['numero' => 'desc']]);

    $salas = Pipeline::make()
        ->send(Sala::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($salas->toArray())->toBe([1, 2]);
});

test('todos os métodos de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive(str()->camel($campo))
        ->withSomeOfArgs('desc')
        ->once();

    request()->merge(['order' => [$campo => 'desc']]);

    Pipeline::make()
        ->send(Sala::query())
        ->through([Order::class])
        ->thenReturn();
})->with([
    'numero',
    'localidade_pai_nome',
    'predio_pai_nome',
    'andar_pai_numero',
    'andar_pai_apelido',
    'estantes_count',
]);

test('todas as ordenações possíveis no request da sala', function () {
    request()->merge(['order' => [
        'numero' => 'asc',
        'localidade_pai_nome' => 'asc',
        'predio_pai_nome' => 'asc',
        'andar_pai_numero' => 'asc',
        'andar_pai_apelido' => 'desc',
        'estantes_count' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(Sala::query())
        ->through([JoinLocalidade::class, Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
