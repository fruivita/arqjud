<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Lotacao;
use App\Pipes\Lotacao\JoinAll;
use App\Pipes\Lotacao\Order;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('ordena pelo nome da lotação', function () {
    Lotacao::factory()->create(['id' => 1, 'nome' => 'bbbb']);
    Lotacao::factory()->create(['id' => 2, 'nome' => 'aaaa']);

    request()->merge(['order' => ['nome' => 'asc']]);

    $lotacoes = Pipeline::make()
        ->send(Lotacao::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($lotacoes->toArray())->toMatchArray([2, 1]);
});

test('todos os métodos de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive(str()->camel($campo))
        ->withSomeOfArgs('desc')
        ->once();

    request()->merge(['order' => [$campo => 'desc']]);

    Pipeline::make()
        ->send(Lotacao::query())
        ->through([Order::class])
        ->thenReturn();
})->with([
    'nome',
    'sigla',
    'administravel',
    'lotacao_pai_nome',
    'lotacao_pai_sigla',
    'usuarios_count',
]);

test('todas as ordenações possíveis no request da lotação', function () {
    request()->merge(['order' => [
        'nome' => 'asc',
        'sigla' => 'asc',
        'administravel' => 'asc',
        'lotacao_pai_nome' => 'desc',
        'lotacao_pai_sigla' => 'desc',
        'usuarios_count' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(Lotacao::query())
        ->through([JoinAll::class, Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
