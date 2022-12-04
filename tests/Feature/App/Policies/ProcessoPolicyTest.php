<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Permissao;
use App\Models\Processo;
use App\Models\Solicitacao;
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
test('usuário sem permissão não pode listar os processos', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Processo::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar um processo', function () {
    expect(Auth::user()->can(Policy::View->value, Processo::class))->toBeFalse();
});

test('usuário sem permissão não pode criar um processo', function () {
    expect(Auth::user()->can(Policy::Create->value, Processo::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar um processo', function () {
    expect(Auth::user()->can(Policy::Update->value, Processo::class))->toBeFalse();
});

test('usuário sem permissão não pode visualizar ou atualizar um processo', function () {
    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Processo::class))->toBeFalse();
});

test('usuário sem permissão não pode excluir um processo', function () {
    $processo = Processo::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $processo))->toBeFalse();
});

test('processo com processos filho não pode ser excluído, independente de permissão', function () {
    concederPermissao(Permissao::PROCESSO_DELETE);

    $processo = Processo::factory()->has(Processo::factory(2), 'processosFilho')->create();

    expect(Auth::user()->can(Policy::Delete->value, $processo))->toBeFalse();
});

test('processo com solicitações (solicitadas, entregues ou devolvidas) não pode ser excluído, independente de permissão', function () {
    concederPermissao(Permissao::PROCESSO_DELETE);

    $processo = Processo::factory()->has(Solicitacao::factory()->solicitada(), 'solicitacoes')->create();
    expect(Auth::user()->can(Policy::Delete->value, $processo))->toBeFalse();

    $processo = Processo::factory()->has(Solicitacao::factory()->entregue(), 'solicitacoes')->create();
    expect(Auth::user()->can(Policy::Delete->value, $processo))->toBeFalse();

    $processo = Processo::factory()->has(Solicitacao::factory()->devolvida(), 'solicitacoes')->create();
    expect(Auth::user()->can(Policy::Delete->value, $processo))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar os processos', function () {
    concederPermissao(Permissao::PROCESSO_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Processo::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um processo', function () {
    concederPermissao(Permissao::PROCESSO_VIEW);

    expect(Auth::user()->can(Policy::View->value, Processo::class))->toBeTrue();
});

test('usuário com permissão pode criar um processo', function () {
    concederPermissao(Permissao::PROCESSO_CREATE);

    expect(Auth::user()->can(Policy::Create->value, Processo::class))->toBeTrue();
});

test('usuário com permissão pode atualizar um processo', function () {
    concederPermissao(Permissao::PROCESSO_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Processo::class))->toBeTrue();
});

test('usuário com permissão pode visualizar um processo por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PROCESSO_VIEW);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Processo::class))->toBeTrue();
});

test('usuário com permissão pode atualizar um processo por meio da policy viewOrUpdate', function () {
    concederPermissao(Permissao::PROCESSO_UPDATE);

    expect(Auth::user()->can(Policy::ViewOrUpdate->value, Processo::class))->toBeTrue();
});

test('usuário com permissão pode excluir um processo sem processos filhos ou solicitações', function () {
    concederPermissao(Permissao::PROCESSO_DELETE);

    $processo = Processo::factory()->create();

    expect(Auth::user()->can(Policy::Delete->value, $processo))->toBeTrue();
});
