<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Guia;
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
test('usuário sem permissão não pode listar as guias', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Guia::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar uma guia', function () {
    expect(Auth::user()->can(Policy::View->value, Guia::class))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as guias', function () {
    concederPermissao(Permissao::GUIA_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Guia::class))->toBeTrue();
});

test('usuário com permissão pode visualizar uma guia', function () {
    concederPermissao(Permissao::GUIA_VIEW);

    expect(Auth::user()->can(Policy::View->value, Guia::class))->toBeTrue();
});
