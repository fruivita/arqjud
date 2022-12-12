<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Solicitacao\JoinAll;
use App\Filters\Solicitacao\Order;
use App\Models\Solicitacao;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo status', function (string $coluna, string $direcao) {
    $entregue = Solicitacao::factory()->entregue()->create();
    $devolvida = Solicitacao::factory()->devolvida()->create();
    $solicitada = Solicitacao::factory()->solicitada()->create();

    request()->merge(['order' => [$coluna, $direcao]]);

    $solicitacoes = Pipeline::make()
        ->send(Solicitacao::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($solicitacoes->toArray())->toBe([
        $solicitada->id,
        $entregue->id,
        $devolvida->id,
    ]);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pela data de solicitação', function () {
    Solicitacao::factory()->create(['id' => 1, 'solicitada_em' => '2020-01-20']);
    Solicitacao::factory()->create(['id' => 2, 'solicitada_em' => '2020-01-15']);

    request()->merge(['order' => ['solicitadaEm' => 'desc']]);

    $solicitacoes = Pipeline::make()
        ->send(Solicitacao::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($solicitacoes->toArray())->toBe([1, 2]);
});

test('todos os métodos de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive(str()->camel($campo))
        ->withSomeOfArgs('desc')
        ->once();

    request()->merge(['order' => [$campo => 'desc']]);

    Pipeline::make()
        ->send(Solicitacao::query())
        ->through([Order::class])
        ->thenReturn();
})->with([
    'solicitada_em',
    'entregue_em',
    'devolvida_em',
    'por_guia',
    'processo_numero',
    'solicitante_sigla',
    'recebedor_sigla',
    'remetente_sigla',
    'rearquivador_sigla',
    'lotacao_destinataria_sigla',
]);

test('todas as ordenações possíveis no request da solicitação', function () {
    request()->merge(['order' => [
        'solicitada_em' => 'asc',
        'entregue_em' => 'asc',
        'devolvida_em' => 'asc',
        'por_guia' => 'asc',
        'processo_numero' => 'desc',
        'solicitante_sigla' => 'desc',
        'recebedor_sigla' => 'desc',
        'remetente_sigla' => 'desc',
        'rearquivador_sigla' => 'desc',
        'lotacao_destinataria_sigla' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(Solicitacao::query())
        ->through([JoinAll::class, Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
