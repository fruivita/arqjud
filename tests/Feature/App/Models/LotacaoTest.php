<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Lotacao;

// Caminho feliz
test('uma lotação pode ser destinatária de várias solicitações de processo', function () {
    $lotacao = Lotacao::factory()->hasSolicitacoes(3)->create();

    $lotacao->load('solicitacoes');

    expect($lotacao->solicitacoes)->toHaveCount(3);
});
