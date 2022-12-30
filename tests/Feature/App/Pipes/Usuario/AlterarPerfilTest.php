<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Perfil;
use App\Models\Usuario;
use App\Pipes\Usuario\AlterarPerfil;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('altera o perfil do usuário', function () {
    $usuario = Usuario::factory()->create();
    $perfil = Perfil::factory()->create();

    Pipeline::make()
        ->send($usuario)
        ->through([AlterarPerfil::class . ':' . $perfil->id])
        ->thenReturn();

    $usuario->refresh();

    expect($usuario->perfil_id)->toBe($perfil->id);
});
