<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\TipoProcesso;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());
});

afterEach(fn () => logout());

// Proibido
test('usuário sem permissão não pode listar os tipos de processo', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, TipoProcesso::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar um tipo de processo', function () {
    expect(Auth::user()->can(Policy::View->value, TipoProcesso::class))->toBeFalse();
});

test('usuário sem permissão não pode criar um tipo de processo', function () {
    expect(Auth::user()->can(Policy::Create->value, TipoProcesso::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um tipo de processo', function () {
    expect(Auth::user()->can(Policy::Update->value, TipoProcesso::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar um tipo de processo', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, TipoProcesso::class))->toBeFalse();
});

test('usuário sem permissão não pode excluir um tipo de processo', function () {
    $tipo_processo = TipoProcesso::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $tipo_processo))->toBeFalse();
});

test('tipo de processo com caixas não pode ser excluído, independente de permissão', function () {
    concederPermissao(Permissao::TIPO_PROCESSO_DELETE);

    $tipo_processo = TipoProcesso::factory()->hasCaixas(2)->create();

    expect(Auth::user()->can(Policy::Delete->value, $tipo_processo))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os tipos de processo', function () {
    concederPermissao(Permissao::TIPO_PROCESSO_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, TipoProcesso::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um tipo de processo', function () {
    concederPermissao(Permissao::TIPO_PROCESSO_VIEW);

    expect(Auth::user()->can(Policy::View->value, TipoProcesso::class))->toBeTrue();
});

test('usuário com permissão pode criar um tipo de processo', function () {
    concederPermissao(Permissao::TIPO_PROCESSO_CREATE);

    expect(Auth::user()->can(Policy::Create->value, TipoProcesso::class))->toBeTrue();
});

test('usuário com permissão pode atualizar um tipo de processo', function () {
    concederPermissao(Permissao::TIPO_PROCESSO_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, TipoProcesso::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um tipo de processo por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::TIPO_PROCESSO_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, TipoProcesso::class))->toBeTrue();
});

test('usuário com permissão pode atualizar um tipo de processo por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::TIPO_PROCESSO_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, TipoProcesso::class))->toBeTrue();
});

test('usuário com permissão pode excluir um tipo de processo sem caixas', function () {
    concederPermissao(Permissao::TIPO_PROCESSO_DELETE);

    $tipo_processo = TipoProcesso::factory()->create();


    expect(Auth::user()->can(Policy::Delete->value, $tipo_processo))->toBeTrue();
});
