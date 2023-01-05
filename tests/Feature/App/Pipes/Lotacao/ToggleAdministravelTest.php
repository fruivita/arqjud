<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Lotacao;
use App\Pipes\Lotacao\ToggleAdministravel;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('faz o toggle da propriedadde administravel', function (bool $value) {
    $lotacao = Lotacao::factory()->create(['administravel' => $value]);

    Pipeline::make()
        ->send($lotacao)
        ->through([ToggleAdministravel::class])
        ->thenReturn();

    $lotacao->refresh();

    expect($lotacao->administravel)->toBe(!$value);
})->with([true, false]);
