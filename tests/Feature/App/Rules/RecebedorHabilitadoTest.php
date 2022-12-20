<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Rules\RecebedorHabilitado;
use Illuminate\Support\Facades\Validator;

// Caminho feliz
test('recebedor sem lotação não está habilitado a receber os processos solicitados', function () {
    $recebedor = Usuario::factory()->create(['lotacao_id' => null]);

    $validator = Validator::make(['recebedor' => $recebedor->username], [
        'recebedor' => [new RecebedorHabilitado()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('recebedor com lotação está habilitado a receber os processos solicitados', function () {
    $recebedor = Usuario::factory()->create();

    $validator = Validator::make(['recebedor' => $recebedor->username], [
        'recebedor' => [new RecebedorHabilitado()],
    ]);

    expect($validator->passes())->toBeTrue();
});

test('mensagem de falha de validação está definida', function () {
    $recebedor = Usuario::factory()->create(['lotacao_id' => null]);

    $validator = Validator::make(['recebedor' => $recebedor->username], [
        'recebedor' => [new RecebedorHabilitado()],
    ]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first())->toBe(__('validation.autorizacao'));
});
