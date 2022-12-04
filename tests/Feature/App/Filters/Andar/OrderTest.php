<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Andar\JoinLocalidade;
use App\Filters\Andar\Order;
use App\Models\Andar;
use Illuminate\Pipeline\Pipeline;
use Mockery\MockInterface;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    Andar::factory()->create(['id' => 1]);
    Andar::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $andares = app(Pipeline::class)
        ->send(Andar::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($andares->toArray())->toBe([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo número', function () {
    Andar::factory()->create(['id' => 1, 'numero' => 20]);
    Andar::factory()->create(['id' => 2, 'numero' => 10]);

    request()->merge(['order' => ['numero' => 'desc']]);

    $andares = app(Pipeline::class)
        ->send(Andar::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($andares->toArray())->toBe([1, 2]);
});

test('todos os método de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class, function (MockInterface $mock) use ($campo) {
        $mock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive(str()->camel($campo))
            ->withSomeOfArgs('desc')
            ->once();
    });

    request()->merge(['order' => [$campo => 'desc']]);

    app(Pipeline::class)
        ->send(Andar::query())
        ->through([Order::class])
        ->thenReturn();
})->with([
    'numero',
    'apelido',
    'localidade_pai_nome',
    'predio_pai_nome',
    'salas_count',
]);

test('todas as ordenações possíveis no request do andar', function () {
    request()->merge(['order' => [
        'numero' => 'asc',
        'apelido' => 'asc',
        'localidade_pai_nome' => 'asc',
        'predio_pai_nome' => 'desc',
        'salas_count' => 'desc',
    ]]);

    $query = app(Pipeline::class)
        ->send(Andar::query())
        ->through([JoinLocalidade::class, Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
