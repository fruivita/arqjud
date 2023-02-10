<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeAll(fn () => \Spatie\Once\Cache::getInstance()->disable());

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = login();
});

afterEach(fn () => logout());

// Proibido
test('usuário sem permissão não pode listar os logs de funcionamento', function () {
    expect(Auth::user()->can(Policy::LogViewAny->value))->toBeFalse();
});

test('usuário sem permissão não pode visualizar um log de funcionamento', function () {
    expect(Auth::user()->can(Policy::LogView->value))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os logs de funcionamento', function () {
    concederPermissao(Permissao::LOG_VIEW_ANY);

    expect(Auth::user()->can(Policy::LogViewAny->value))->toBeTrue();
});

test('usuário com permissão pode visualizar um log de funcionamento', function () {
    concederPermissao(Permissao::LOG_VIEW);

    expect(Auth::user()->can(Policy::LogView->value))->toBeTrue();
});
