<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Localidade;
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
test('usuário sem permissão não pode listar as localidades', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Localidade::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar individualmente uma localidade', function () {
    expect(Auth::user()->can(Policy::View->value, Localidade::class))->toBeFalse();
});

test('usuário sem permissão não pode criar uma localidade', function () {
    expect(Auth::user()->can(Policy::Create->value, Localidade::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma localidade', function () {
    expect(Auth::user()->can(Policy::Update->value, Localidade::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar uma localidade', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Localidade::class))->toBeFalse();
});

test('usuário sem permissão não pode excluir uma localidade', function () {
    $localidade = Localidade::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $localidade))->toBeFalse();
});

// test('localidade com prédios não pode ser excluída, independente de permissão', function () {
//     concederPermissao(Permissao::LocalidadeDelete);

//     $localidade = Localidade::factory()->has(Predio::factory(2), 'predios')->create();

//     expect(Auth::user()->can(Policy::Delete->value, $localidade))->toBeFalse();
// });

// test('localidade com caixas criadas pode ser excluída, independente de permissão', function () {
//     concederPermissao(Permissao::LocalidadeDelete);

//     $localidade = Localidade::factory()->has(Caixa::factory(2), 'caixasCriadas')->create();

//     expect(Auth::user()->can(Policy::Delete->value, $localidade))->toBeFalse();
// });

// Caminho feliz
test('usuário com permissão pode listar as localidades', function () {
    concederPermissao(Permissao::LOCALIDADE_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Localidade::class))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma localidade', function () {
    concederPermissao(Permissao::LOCALIDADE_VIEW);

    expect(Auth::user()->can(Policy::View->value, Localidade::class))->toBeTrue();
});

test('usuário com permissão pode criar uma localidade', function () {
    concederPermissao(Permissao::LOCALIDADE_CREATE);

    expect(Auth::user()->can(Policy::Create->value, Localidade::class))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma localidade', function () {
    concederPermissao(Permissao::LOCALIDADE_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Localidade::class))->toBeTrue();
});

test('usuário com permissão pode individualmente visualizar uma localidade por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::LOCALIDADE_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Localidade::class))->toBeTrue();
});

test('usuário com permissão pode individualmente atualizar uma localidade por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::LOCALIDADE_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Localidade::class))->toBeTrue();
});

test('usuário com permissão pode individualmente excluir uma localidade sem prédios e sem caixas criadas', function () {
    concederPermissao(Permissao::LOCALIDADE_DELETE);

    $localidade = Localidade::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $localidade))->toBeTrue();
});
