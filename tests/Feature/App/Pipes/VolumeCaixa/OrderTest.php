<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\VolumeCaixa;
use App\Pipes\VolumeCaixa\JoinLocalidade;
use App\Pipes\VolumeCaixa\Order;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    VolumeCaixa::factory()->create(['id' => 1]);
    VolumeCaixa::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $volumes = Pipeline::make()
        ->send(VolumeCaixa::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($volumes->toArray())->toBe([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo número', function () {
    VolumeCaixa::factory()->create(['id' => 1, 'numero' => 20]);
    VolumeCaixa::factory()->create(['id' => 2, 'numero' => 10]);

    request()->merge(['order' => ['numero' => 'desc']]);

    $volumes = Pipeline::make()
        ->send(VolumeCaixa::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($volumes->toArray())->toBe([1, 2]);
});

test('todos os métodos de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive(str()->camel($campo))
        ->withSomeOfArgs('desc')
        ->once();

    request()->merge(['order' => [$campo => 'desc']]);

    Pipeline::make()
        ->send(VolumeCaixa::query())
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
    'prateleira_pai_numero',
    'caixa_pai_numero',
    'caixa_pai_ano',
    'caixa_pai_guarda_permanente',
    'caixa_pai_complemento',
    'caixa_pai_localidade_criadora_nome',
    'processos_count',
]);

test('todas as ordenações possíveis no request do volume da caixa', function () {
    request()->merge(['order' => [
        'numero' => 'asc',
        'localidade_pai_nome' => 'asc',
        'predio_pai_nome' => 'asc',
        'andar_pai_numero' => 'asc',
        'andar_pai_apelido' => 'desc',
        'sala_pai_numero' => 'desc',
        'estante_pai_numero' => 'desc',
        'prateleira_pai_numero' => 'desc',
        'caixa_pai_numero' => 'desc',
        'caixa_pai_ano' => 'desc',
        'caixa_pai_guarda_permanente' => 'desc',
        'caixa_pai_complemento' => 'desc',
        'caixa_pai_localidade_criadora_nome' => 'desc',
        'processos_count' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(VolumeCaixa::query())
        ->through([JoinLocalidade::class, Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
