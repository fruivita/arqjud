<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Lotacao;
use App\Models\Usuario;
use App\Rules\UsuarioHabilitado;
use Illuminate\Support\Facades\Validator;

// Caminho feliz
test('usuario sem lotação não está habilitado a interagir na remessa de processos', function () {
    $usuario = Usuario::factory()->create(['lotacao_id' => null]);

    $validator = Validator::make(['usuario' => $usuario->matricula], [
        'usuario' => [new UsuarioHabilitado()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('usuario com lotação inválida (zero) não está habilitado a interagir na remessa de processos', function () {
    $usuario = Usuario::factory()->create(['lotacao_id' => Lotacao::factory()->create(['id' => 0])]);

    $validator = Validator::make(['usuario' => $usuario->matricula], [
        'usuario' => [new UsuarioHabilitado()],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('usuario sem email não está habilitado a interagir na remessa de processos', function (mixed $email) {
    $usuario = Usuario::factory()->create(['email' => $email]);

    $validator = Validator::make(['usuario' => $usuario->matricula], [
        'usuario' => [new UsuarioHabilitado()],
    ]);

    expect($validator->passes())->toBeFalse();
})->with([null, '']);

test('usuario sem nome não está habilitado a interagir na remessa de processos', function (mixed $nome) {
    $usuario = Usuario::factory()->create(['nome' => $nome]);

    $validator = Validator::make(['usuario' => $usuario->matricula], [
        'usuario' => [new UsuarioHabilitado()],
    ]);

    expect($validator->passes())->toBeFalse();
})->with([null, '']);

test('usuário com todos os campos esttá habilitado a interagir na remessa de processos', function () {
    $usuario = Usuario::factory()->create();

    $validator = Validator::make(['usuario' => $usuario->matricula], [
        'usuario' => [new UsuarioHabilitado()],
    ]);

    expect($validator->passes())->toBeTrue();
});

test('mensagem de falha de validação está definida', function () {
    $usuario = Usuario::factory()->create(['lotacao_id' => null]);

    $validator = Validator::make(['usuario' => $usuario->matricula], [
        'usuario' => [new UsuarioHabilitado()],
    ]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first())->toBe(__('validation.habilitado'));
});
