<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Permissao;
use Database\Seeders\ConfiguracaoSeeder;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([ConfiguracaoSeeder::class, LotacaoSeeder::class, PerfilSeeder::class]);

    $this->usuario = login('dumb user');

    $this->usuario->refresh();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('super admin ignora as verificações de permissão mesmo sem ter nenhuma permissão', function () {
    expect($this->usuario->perfil->permissoes)->toBeEmpty()
    ->and($this->usuario->can(Policy::Update, Permissao::class))->toBeTrue();
});
