<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Permissao;
use App\Models\Atividade;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());
});

afterEach(fn () => logout());

// Proibido
test('usuário sem permissão não pode listar os logs de atividade', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Atividade::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar um log de atividade', function () {
    expect(Auth::user()->can(Policy::View->value, Atividade::class))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os logs de atividade', function () {
    concederPermissao(Permissao::ATIVIDADE_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Atividade::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um log de atividade', function () {
    concederPermissao(Permissao::ATIVIDADE_VIEW);

    expect(Auth::user()->can(Policy::View->value, Atividade::class))->toBeTrue();
});
