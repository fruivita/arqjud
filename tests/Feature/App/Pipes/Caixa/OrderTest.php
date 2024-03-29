<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Caixa;
use App\Pipes\Caixa\JoinLocalidade;
use App\Pipes\Caixa\Order;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    Caixa::factory()->create(['id' => 1]);
    Caixa::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $caixas = Pipeline::make()
        ->send(Caixa::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($caixas->toArray())->toMatchArray([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo número', function () {
    Caixa::factory()->create(['id' => 1, 'numero' => 20]);
    Caixa::factory()->create(['id' => 2, 'numero' => 10]);

    request()->merge(['order' => ['numero' => 'desc']]);

    $caixas = Pipeline::make()
        ->send(Caixa::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($caixas->toArray())->toMatchArray([1, 2]);
});

test('todos os métodos de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive(str()->camel($campo))
        ->withSomeOfArgs('desc')
        ->once();

    request()->merge(['order' => [$campo => 'desc']]);

    Pipeline::make()
        ->send(Caixa::query())
        ->through([Order::class])
        ->thenReturn();
})->with([
    'numero',
    'ano',
    'guarda_permanente',
    'complemento',
    'localidade_pai_nome',
    'predio_pai_nome',
    'andar_pai_numero',
    'andar_pai_apelido',
    'sala_pai_numero',
    'estante_pai_numero',
    'prateleira_pai_numero',
    'localidade_criadora_nome',
    'tipo_processo_nome',
    'processos_count',
]);

test('todas as ordenações possíveis no request da caixa', function () {
    request()->merge(['order' => [
        'numero' => 'asc',
        'ano' => 'asc',
        'guarda_permanente' => 'asc',
        'complemento' => 'asc',
        'localidade_pai_nome' => 'asc',
        'predio_pai_nome' => 'asc',
        'andar_pai_numero' => 'asc',
        'andar_pai_apelido' => 'desc',
        'sala_pai_numero' => 'desc',
        'estante_pai_numero' => 'desc',
        'prateleira_pai_numero' => 'desc',
        'localidade_criadora_nome' => 'desc',
        'tipo_processo_nome' => 'desc',
        'processos_count' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(Caixa::query())
        ->through([JoinLocalidade::class, Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
