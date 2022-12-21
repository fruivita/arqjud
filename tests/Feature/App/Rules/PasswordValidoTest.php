<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\PasswordValido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

// Caminho feliz
test('identifica credenciais inválidas', function () {
    Auth::partialMock() // @phpstan-ignore-line
        ->shouldReceive('validate')
        ->once()
        ->andReturnFalse();

    $validator = Validator::make(['password' => 'bar'], [
        'password' => [new PasswordValido('foo')],
    ]);

    expect($validator->passes())->toBeFalse();
});

test('identifica credenciais válidas', function () {
    Auth::partialMock() // @phpstan-ignore-line
        ->shouldReceive('validate')
        ->once()
        ->andReturnTrue();

    $validator = Validator::make(['password' => 'bar'], [
        'password' => [new PasswordValido('foo')],
    ]);

    expect($validator->passes())->toBeTrue();
});

test('mensagem de falha de validação está definida', function () {
    Auth::partialMock() // @phpstan-ignore-line
        ->shouldReceive('validate')
        ->once()
        ->andReturnFalse();

    $validator = Validator::make(['password' => 'bar'], [
        'password' => [new PasswordValido('foo')],
    ]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first())->toBe(__('auth.password'));
});
