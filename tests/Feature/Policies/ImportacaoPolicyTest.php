<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Policies\ImportacaoPolicy;
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
test('usuário sem permição não pode realizar uma importação', function () {
    expect((new ImportacaoPolicy())->create($this->usuario))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode realizar uma importação', function () {
    concederPermissao(Permissao::ImportacaoCreate->value);

    expect((new ImportacaoPolicy())->create($this->usuario))->toBeTrue();
});
