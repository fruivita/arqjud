<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Policies\SimulacaoPolicy;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->usuario = login('foo');
});

afterEach(function () {
    logout();
});

// Proibido
test('usuário sem permissão não pode criar uma simulação', function () {
    expect((new SimulacaoPolicy())->create($this->usuario))->toBeFalse();
});

test('usuário não pode simultaneamente criar duas simulações na mesma sessão', function () {
    concederPermissao(Permissao::SimulacaoCreate->value);
    session()->put('simulado', 'bar');

    expect((new SimulacaoPolicy())->create($this->usuario))->toBeFalse();
});

test('usuário não pode desfazer simulação que não existe em sua sessão', function () {
    expect((new SimulacaoPolicy())->delete($this->usuario))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode criar uma simulação', function () {
    concederPermissao(Permissao::SimulacaoCreate->value);

    expect((new SimulacaoPolicy())->create($this->usuario))->toBeTrue();
});

test('usuário pode desfazer simulação existente em sua sessão', function () {
    session()->put('simulador', 'baz');

    expect((new SimulacaoPolicy())->delete($this->usuario))->toBeTrue();
});
