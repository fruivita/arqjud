<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = login();
});

afterEach(fn () => logout());

// Proibido
test('usuário sem permissão não pode executar a importação de dados', function () {
    expect(Auth::user()->can(Policy::ImportacaoCreate->value))->toBeFalse();
});

// Caminho feliz
test('usuário pode executar a importação de dados', function () {
    concederPermissao(Permissao::IMPORTACAO_CREATE);

    expect(Auth::user()->can(Policy::ImportacaoCreate->value))->toBeTrue();
});
