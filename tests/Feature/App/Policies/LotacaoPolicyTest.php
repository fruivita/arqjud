<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Lotacao;
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
test('usuário sem permissão não pode listar as lotações', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Lotacao::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma lotação', function () {
    expect(Auth::user()->can(Policy::Update->value, Lotacao::class))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as lotações', function () {
    concederPermissao(Permissao::LOTACAO_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Lotacao::class))->toBeTrue();
});

test('usuário com permissão pode atualizar uma lotação', function () {
    concederPermissao(Permissao::LOTACAO_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Lotacao::class))->toBeTrue();
});
