<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Processo;
use App\Pipes\Processo\JoinLocalidade;
use App\Pipes\Processo\Order;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    Processo::factory()->create(['id' => 1]);
    Processo::factory()->create(['id' => 2]);

    request()->merge(['order' => [$coluna, $direcao]]);

    $volumes = Pipeline::make()
        ->send(Processo::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($volumes->toArray())->toMatchArray([2, 1]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo número', function () {
    Processo::factory()->create(['id' => 1, 'numero' => '20']);
    Processo::factory()->create(['id' => 2, 'numero' => '10']);

    request()->merge(['order' => ['numero' => 'desc']]);

    $volumes = Pipeline::make()
        ->send(Processo::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($volumes->toArray())->toMatchArray([1, 2]);
});

test('todos os métodos de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive(str()->camel($campo))
        ->withSomeOfArgs('desc')
        ->once();

    request()->merge(['order' => [$campo => 'desc']]);

    Pipeline::make()
        ->send(Processo::query())
        ->through([Order::class])
        ->thenReturn();
})->with([
    'numero',
    'numero_antigo',
    'arquivado_em',
    'guarda_permanente',
    'qtd_volumes',
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
    'volume_caixa_pai_numero',
    'processos_filho_count',
    'solicitacoes_count',
]);

test('todas as ordenações possíveis no request do processo', function () {
    request()->merge(['order' => [
        'numero' => 'asc',
        'numero_antigo' => 'asc',
        'arquivado_em' => 'asc',
        'guarda_permanente' => 'asc',
        'qtd_volumes' => 'asc',
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
        'volume_caixa_pai_numero' => 'desc',
        'processos_filho_count' => 'desc',
        'solicitacoes_count' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(Processo::query())
        ->through([JoinLocalidade::class, Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
