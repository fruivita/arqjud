<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Permissao;
use App\Models\VolumeCaixa;
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
test('usuário sem permissão não pode listar os volumes de caixa', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, VolumeCaixa::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar um volume de caixa', function () {
    expect(Auth::user()->can(Policy::View->value, VolumeCaixa::class))->toBeFalse();
});

test('usuário sem permissão não pode criar um volume de caixa', function () {
    expect(Auth::user()->can(Policy::Create->value, VolumeCaixa::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um volume de caixa', function () {
    expect(Auth::user()->can(Policy::Update->value, VolumeCaixa::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar um volume de caixa', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, VolumeCaixa::class))->toBeFalse();
});

test('usuário sem permissão não pode excluir um volume de caixa', function () {
    $volume = VolumeCaixa::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $volume))->toBeFalse();
});

test('volume de caixa com processos não pode ser excluído, independente de permissão', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_DELETE);

    $volume = VolumeCaixa::factory()->hasProcessos(2)->create();

    expect(Auth::user()->can(Policy::Delete->value, $volume))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os volumes de caixa', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, VolumeCaixa::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um volume de caixa', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_VIEW);

    expect(Auth::user()->can(Policy::View->value, VolumeCaixa::class))->toBeTrue();
});

test('usuário com permissão pode criar um volume de caixa', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_CREATE);

    expect(Auth::user()->can(Policy::Create->value, VolumeCaixa::class))->toBeTrue();
});

test('usuário com permissão pode atualizar um volume de caixa', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, VolumeCaixa::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um volume de caixa por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, VolumeCaixa::class))->toBeTrue();
});

test('usuário com permissão pode atualizar um volume de caixa por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, VolumeCaixa::class))->toBeTrue();
});

test('usuário com permissão pode excluir um volume de caixa', function () {
    concederPermissao(Permissao::VOLUME_CAIXA_DELETE);

    $volume = VolumeCaixa::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $volume))->toBeTrue();
});
