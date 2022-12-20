<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Rules\SolicitacaoEntregavel;
use Illuminate\Support\Facades\Validator;

// Caminho feliz
test('não se pode entregar processo com a solicitação no status entregue', function () {
    $solicitacao = Solicitacao::factory()->entregue()->create();
    $recebedor = Usuario::factory()->create(['lotacao_id' => $solicitacao->lotacao_destinataria_id]);

    $validator = Validator::make(['solicitacao_id' => $solicitacao->id, 'recebedor' => $recebedor->username], [
        'solicitacao_id' => [new SolicitacaoEntregavel()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('não se pode entregar processo com a solicitação no status devolvida', function () {
    $solicitacao = Solicitacao::factory()->devolvida()->create();
    $recebedor = Usuario::factory()->create(['lotacao_id' => $solicitacao->lotacao_destinataria_id]);

    $validator = Validator::make(['solicitacao_id' => $solicitacao->id, 'recebedor' => $recebedor->username], [
        'solicitacao_id' => [new SolicitacaoEntregavel()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('não se pode entregar processo para recebedor de lotação diversa da lotação destinatária', function () {
    $solicitacao = Solicitacao::factory()->entregue()->create();
    $recebedor = Usuario::factory()->create();

    $validator = Validator::make(['solicitacao_id' => $solicitacao->id, 'recebedor' => $recebedor->username], [
        'solicitacao_id' => [new SolicitacaoEntregavel()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('solicitação é entregável apenas se no status solicitada e o recebedor for da lotação destinatária', function () {
    $solicitacao = Solicitacao::factory()->solicitada()->create();
    $recebedor = Usuario::factory()->create(['lotacao_id' => $solicitacao->lotacao_destinataria_id]);

    $validator = Validator::make(['solicitacao_id' => $solicitacao->id, 'recebedor' => $recebedor->username], [
        'solicitacao_id' => [new SolicitacaoEntregavel()],
    ]);

    expect($validator->passes())->toBeTrue();
});

test('mensagem de falha de validação está definida', function () {
    $solicitacao = Solicitacao::factory()->entregue()->create();
    $recebedor = Usuario::factory()->create();

    $validator = Validator::make(['solicitacao_id' => $solicitacao->id, 'recebedor' => $recebedor->username], [
        'solicitacao_id' => [new SolicitacaoEntregavel()],
    ]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first())->toBe(__('validation.solicitacao.recebedor'));
});
