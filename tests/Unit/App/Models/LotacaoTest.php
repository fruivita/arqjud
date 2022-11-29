<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Lotacao;

// Caminho feliz
test('id da lotação padrão para usuários sem lotação está definido', function () {
    expect(Lotacao::SEM_LOTACAO)->toBe(0);
});
