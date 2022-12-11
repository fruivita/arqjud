<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Guia;
use App\Models\Lotacao;
use App\Models\Processo;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar solicitação com campo inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Solicitacao::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['solicitada_em',    null,             'cannot be null'],           // obrigatório
    ['por_guia', 'foo',                    'Incorrect integer value'],  // não conversível em boolean
    ['por_guia', null,                     'cannot be null'],           // obrigatório
    ['descricao',        Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

test('lança exception ao tentar definir relacionamento inválido', function (string $campo, mixed $valor, string $mensagem) {
    expect(
        fn () => Solicitacao::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['processo_id',             99999999, 'Cannot add or update a child row'], // não existente
    ['processo_id',             null,     'cannot be null'],                   // obrigatório
    ['solicitante_id',          99999999, 'Cannot add or update a child row'], // não existente
    ['solicitante_id',          null,     'cannot be null'],                   // obrigatório
    ['recebedor_id',            99999999, 'Cannot add or update a child row'], // não existente
    ['remetente_id',            99999999, 'Cannot add or update a child row'], // não existente
    ['rearquivador_id',         99999999, 'Cannot add or update a child row'], // não existente
    ['lotacao_destinataria_id', 99999999, 'Cannot add or update a child row'], // não existente
    ['lotacao_destinataria_id', null,     'cannot be null'],                   // obrigatório
    ['guia_id',                 99999999, 'Cannot add or update a child row'], // não existente
]);

// Caminho feliz
test('aceita campos em seus tamanhos máximos', function () {
    Solicitacao::factory()->create(['descricao' => Str::random(255)]);

    expect(Solicitacao::count())->toBe(1);
});

test('campos opcionais estão definidos', function () {
    Solicitacao::factory()->create([
        'entregue_em' => null,
        'devolvida_em' => null,
        'descricao' => null,
    ]);

    expect(Solicitacao::count())->toBe(1);
});

test('relacionamentos opcionais estão definidos', function () {
    Solicitacao::factory()->create([
        'recebedor_id' => null,
        'remetente_id' => null,
        'rearquivador_id' => null,
        'guia_id' => null,
    ]);

    expect(Solicitacao::count())->toBe(1);
});

test('uma solicitação refere-se a um processo', function () {
    $solicitacao = Solicitacao::factory()->create();

    $solicitacao->load(['processo']);

    expect($solicitacao->processo)->toBeInstanceOf(Processo::class);
});

test('uma solicitação é feita por um usuário (solicitante)', function () {
    $solicitacao = Solicitacao::factory()->create();

    $solicitacao->load(['solicitante']);

    expect($solicitacao->solicitante)->toBeInstanceOf(Usuario::class);
});

test('uma solicitação é entregue a um usuário (recebedor)', function () {
    $solicitacao = Solicitacao::factory()->create();

    $solicitacao->load(['recebedor']);

    expect($solicitacao->recebedor)->toBeInstanceOf(Usuario::class);
});

test('uma solicitação é efetivada por um usuário (remetente)', function () {
    $solicitacao = Solicitacao::factory()->create();

    $solicitacao->load(['remetente']);

    expect($solicitacao->remetente)->toBeInstanceOf(Usuario::class);
});

test('uma solicitação é rearquivada por um usuário (rearquivador)', function () {
    $solicitacao = Solicitacao::factory()->create();

    $solicitacao->load(['rearquivador']);

    expect($solicitacao->rearquivador)->toBeInstanceOf(Usuario::class);
});

test('uma solicitação é enviada a uma lotação destinatária', function () {
    $solicitacao = Solicitacao::factory()->create();

    $solicitacao->load(['lotacaoDestinataria']);

    expect($solicitacao->lotacaoDestinataria)->toBeInstanceOf(Lotacao::class);
});

test('uma solicitação é registrada em uma guia de remessa', function () {
    $solicitacao = Solicitacao::factory()->create();

    $solicitacao->load(['guia']);

    expect($solicitacao->guia)->toBeInstanceOf(Guia::class);
});

test('retorna as solicitações pelo escopo solicitadas, entregues, devolvidas e ativas', function () {
    Solicitacao::factory()->solicitada()->create();
    Solicitacao::factory(2)->entregue()->create();
    Solicitacao::factory(4)->devolvida()->create();

    expect(Solicitacao::count())->toBe(7)
        ->and(Solicitacao::solicitadas()->count())->toBe(1)
        ->and(Solicitacao::entregues()->count())->toBe(2)
        ->and(Solicitacao::devolvidas()->count())->toBe(4)
        ->and(Solicitacao::ativas()->count())->toBe(3); // solicitada + entregue
});

test('cast status retorna a string para exibição do status da solicitação', function () {
    $solicitada = Solicitacao::factory()->solicitada()->create();
    $entregue = Solicitacao::factory()->entregue()->create();
    $devolvida = Solicitacao::factory()->devolvida()->create();

    expect($solicitada->status)->toBe(__('solicitada'))
        ->and($entregue->status)->toBe(__('entregue'))
        ->and($devolvida->status)->toBe(__('devolvida'));
});

test('atributos sujeitos ao cast estão definidos', function () {
    $solicitacao = new Solicitacao();

    $solicitacao->solicitada_em = '15-01-1900';
    $solicitacao->entregue_em = '1900-01-20';
    $solicitacao->devolvida_em = '25-01-1900';
    $solicitacao->por_guia = 1;

    expect($solicitacao->solicitada_em)->toBeInstanceOf(Carbon::class)
        ->and($solicitacao->entregue_em)->toBeInstanceOf(Carbon::class)
        ->and($solicitacao->devolvida_em)->toBeInstanceOf(Carbon::class)
        ->and($solicitacao->por_guia)->toBeBool();
});

test('escopo CountAll contabiliza as solicitações por tipo', function () {
    Solicitacao::factory()->solicitada()->create();
    Solicitacao::factory(2)->entregue()->create();
    Solicitacao::factory(4)->devolvida()->create();

    $report = Solicitacao::countAll()->toBase()->first();

    expect($report->solicitadas)->toBe(1)
        ->and($report->entregues)->toBe(2)
        ->and($report->devolvidas)->toBe(4);
});
