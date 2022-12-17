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

afterEach(function () {
    logout();
});

// Proibido
test('usuário sem permissão não pode movimentar processo', function () {
    expect(Auth::user()->can(Policy::MoverProcessoCreate->value))->toBeFalse();
});

// Caminho feliz
test('usuário pode movimentar processo se tiver permissão', function () {
    concederPermissao(Permissao::MOVER_PROCESSO_CREATE);

    expect(Auth::user()->can(Policy::MoverProcessoCreate->value))->toBeTrue();
});
