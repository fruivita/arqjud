<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Permissao;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->usuario = Usuario::factory()->create();
    Auth::login($this->usuario);
});

afterEach(function () {
    Auth::logout();
});

// Proibido
test('usuário sem permissão não pode listar as solicitações', function () {
    expect(Auth::user()->can(Policy::ViewAny->value, Solicitacao::class))->toBeFalse();
});

test('usuário externo ao arquivo sem permissão não pode listar as solicitações de sua lotação', function () {
    expect(Auth::user()->can(Policy::ExternoViewAny->value, Solicitacao::class))->toBeFalse();
});

test('usuário sem permissão não pode criar uma solicitação', function () {
    expect(Auth::user()->can(Policy::Create->value, Solicitacao::class))->toBeFalse();
});

test('usuário externo ao arquivo sem permissão não pode criar uma solicitação para sua lotação', function () {
    expect(Auth::user()->can(Policy::ExternoCreate->value, Solicitacao::class))->toBeFalse();
});

test('usuário sem permissão não pode atualizar uma solicitação', function () {
    expect(Auth::user()->can(Policy::Update->value, Solicitacao::class))->toBeFalse();
});

test('usuário sem permissão não pode excluir uma solicitação', function () {
    $solicitacao = Solicitacao::factory()->solicitada()->create();

    expect(Auth::user()->can(Policy::Delete->value, $solicitacao))->toBeFalse();
});

test('usuário com permissão não pode excluir uma solicitação se ela já foi entregue', function () {
    $solicitacao = Solicitacao::factory()->entregue()->create();

    concederPermissao(Permissao::SOLICITACAO_DELETE);

    expect(Auth::user()->can(Policy::Delete->value, $solicitacao))->toBeFalse();
});

test('usuário externo ao arquivo sem permissão não pode excluir uma solicitação de sua lotação', function () {
    $solicitacao = Solicitacao::factory()->solicitada()->create(['destino_id' => $this->usuario->lotacao_id]);

    expect(Auth::user()->can(Policy::ExternoDelete->value, $solicitacao))->toBeFalse();
});

test('usuário externo ao arquivo com permissão não pode excluir uma solicitação de outra lotação', function () {
    $solicitacao = Solicitacao::factory()->solicitada()->create();

    concederPermissao(Permissao::SOLICITACAO_EXTERNA_DELETE);

    expect(Auth::user()->can(Policy::ExternoDelete->value, $solicitacao))->toBeFalse();
});

test('usuário externo ao arquivo com permissão não pode excluir uma solicitação se ela já foi entregue à sua lotação', function () {
    $solicitacao = Solicitacao::factory()->entregue()->create(['destino_id' => $this->usuario->lotacao_id]);

    concederPermissao(Permissao::SOLICITACAO_EXTERNA_DELETE);

    expect(Auth::user()->can(Policy::ExternoDelete->value, $solicitacao))->toBeFalse();
});

// Caminho feliz
test('usuário com permissão pode listar as solicitações', function () {
    concederPermissao(Permissao::SOLICITACAO_VIEW_ANY);

    expect(Auth::user()->can(Policy::ViewAny->value, Solicitacao::class))->toBeTrue();
});

test('usuário externo ao arquivo com permissão pode listar as solicitações de sua lotação', function () {
    concederPermissao(Permissao::SOLICITACAO_EXTERNA_VIEW_ANY);

    expect(Auth::user()->can(Policy::ExternoViewAny->value, Solicitacao::class))->toBeTrue();
});

test('usuário com permissão pode criar uma solicitação', function () {
    concederPermissao(Permissao::SOLICITACAO_CREATE);

    expect(Auth::user()->can(Policy::Create->value, Solicitacao::class))->toBeTrue();
});

test('usuário externo ao arquivo com permissão pode criar uma solicitação destinada à sua lotação', function () {
    concederPermissao(Permissao::SOLICITACAO_EXTERNA_CREATE);

    expect(Auth::user()->can(Policy::ExternoCreate->value, Solicitacao::class))->toBeTrue();
});

test('usuário com permissão pode atualizar uma solicitação', function () {
    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    expect(Auth::user()->can(Policy::Update->value, Solicitacao::class))->toBeTrue();
});

test('usuário com permissão pode excluir uma solicitação que ainda não tenha sido entregue', function () {
    $solicitacao = Solicitacao::factory()->solicitada()->create();

    concederPermissao(Permissao::SOLICITACAO_DELETE);

    expect(Auth::user()->can(Policy::Delete->value, $solicitacao))->toBeTrue();
});

test('usuário externo ao arquivo com permissão pode excluir solicitação da própria lotação que ainda não tenha sido entregue', function () {
    $solicitacao = Solicitacao::factory()->solicitada()->create(['destino_id' => $this->usuario->lotacao_id]);

    concederPermissao(Permissao::SOLICITACAO_EXTERNA_DELETE);

    expect(Auth::user()->can(Policy::ExternoDelete->value, $solicitacao))->toBeTrue();
});
