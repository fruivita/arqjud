<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Prateleira\JoinLocalidade;
use App\Filters\Prateleira\Order;
use App\Models\Prateleira;
use MichaelRubel\EnhancedPipeline\Pipeline;
use Mockery\MockInterface;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    Prateleira::factory()->create(['id' => 1]);
    Prateleira::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $prateleiras = Pipeline::make()
        ->send(Prateleira::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($prateleiras->toArray())->toBe([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo número', function () {
    Prateleira::factory()->create(['id' => 1, 'numero' => '20']);
    Prateleira::factory()->create(['id' => 2, 'numero' => '10']);

    request()->merge(['order' => ['numero' => 'desc']]);

    $prateleiras = Pipeline::make()
        ->send(Prateleira::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($prateleiras->toArray())->toBe([1, 2]);
});

test('todos os métodos de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive(str()->camel($campo))
        ->withSomeOfArgs('desc')
        ->once();

    request()->merge(['order' => [$campo => 'desc']]);

    Pipeline::make()
        ->send(Prateleira::query())
        ->through([Order::class])
        ->thenReturn();
})->with([
    'numero',
    'localidade_pai_nome',
    'predio_pai_nome',
    'andar_pai_numero',
    'andar_pai_apelido',
    'sala_pai_numero',
    'estante_pai_numero',
    'caixas_count',
]);

test('todas as ordenações possíveis no request da prateleira', function () {
    request()->merge(['order' => [
        'numero' => 'asc',
        'localidade_pai_nome' => 'asc',
        'predio_pai_nome' => 'asc',
        'andar_pai_numero' => 'asc',
        'andar_pai_apelido' => 'desc',
        'sala_pai_numero' => 'desc',
        'estante_pai_numero' => 'desc',
        'caixas_count' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(Prateleira::query())
        ->through([JoinLocalidade::class, Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
