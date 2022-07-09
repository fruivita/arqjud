<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Usuario;
use App\Policies\DelegacaoPolicy;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->usuario = login('foo');
});

afterEach(function () {
    logout();
});

// Proibido
test('usuário sem permissão não pode listar as delegações de sua lotação', function () {
    expect((new DelegacaoPolicy())->viewAny($this->usuario))->toBeFalse();
});

test('usuário não pode delegar perfil se o outro usuário possuir perfil superior', function () {
    $lotacao_a = Lotacao::factory()->create();
    $lotacao_b = Lotacao::factory()->create();

    $delegado = Usuario::factory()->create([
        'lotacao_id' => $lotacao_b->id,
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
    ]);

    $this->usuario->lotacao_id = $lotacao_a->id;
    $this->usuario->perfil_id = Perfil::PADRAO;
    $this->usuario->save();

    concederPermissao(Permissao::DelegacaoCreate->value);

    expect((new DelegacaoPolicy())->create($this->usuario, $delegado))->toBeFalse();
});

test('usuário não pode delegar perfil para usuário de outra lotação', function () {
    $lotacao_a = Lotacao::factory()->create();
    $lotacao_b = Lotacao::factory()->create();

    $this->usuario->lotacao_id = $lotacao_a->id;
    $this->usuario->perfil_id = Perfil::GERENTE_NEGOCIO;
    $this->usuario->save();

    concederPermissao(Permissao::DelegacaoCreate->value);

    $delegado = Usuario::factory()->create([
        'lotacao_id' => $lotacao_b->id,
        'perfil_id' => Perfil::PADRAO,
    ]);

    expect((new DelegacaoPolicy())->create($this->usuario, $delegado))->toBeFalse();
});

test('usuário não pode delegar perfil sem permissão específica', function () {
    $lotacao = Lotacao::factory()->create();

    $this->usuario->lotacao_id = $lotacao->id;
    $this->usuario->perfil_id = Perfil::ADMINISTRADOR;
    $this->usuario->save();

    $delegado = Usuario::factory()->create([
        'lotacao_id' => $lotacao->id,
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
    ]);

    expect((new DelegacaoPolicy())->create($this->usuario, $delegado))->toBeFalse();
});

test('usuário não pode delegar permissão delegada', function () {
    $lotacao = Lotacao::factory()->create();

    $this->usuario->lotacao_id = $lotacao->id;
    $this->usuario->perfil_id = Perfil::ADMINISTRADOR;
    $this->usuario->save();

    concederPermissao(Permissao::DelegacaoCreate->value);

    logout();

    $delegado_ativo = login('bar');

    $delegado_ativo->lotacao_id = $lotacao->id;
    $delegado_ativo->perfil_id = Perfil::ADMINISTRADOR;
    $delegado_ativo->perfil_concedido_por = $this->usuario->id;
    $delegado_ativo->antigo_perfil_id = Perfil::GERENTE_NEGOCIO;
    $delegado_ativo->save();

    $delegado = Usuario::factory()->create([
        'lotacao_id' => $lotacao->id,
        'perfil_id' => Perfil::OBSERVADOR,
    ]);

    expect((new DelegacaoPolicy())->create($delegado_ativo, $delegado))->toBeFalse();
});

test('usuário não pode remover deleção inexistente', function () {
    $lotacao = Lotacao::factory()->create();

    $this->usuario->lotacao_id = $lotacao->id;
    $this->usuario->perfil_id = Perfil::ADMINISTRADOR;
    $this->usuario->save();

    $delegado = Usuario::factory()->create([
        'lotacao_id' => $lotacao->id,
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
    ]);

    expect((new DelegacaoPolicy())->delete($this->usuario, $delegado))->toBeFalse();
});

test('usuário não pode remover delegação de perfil superior', function () {
    $lotacao = Lotacao::factory()->create();

    $this->usuario->lotacao_id = $lotacao->id;
    $this->usuario->perfil_id = Perfil::GERENTE_NEGOCIO;
    $this->usuario->save();

    $delegante = Usuario::factory()->create([
        'lotacao_id' => $lotacao->id,
        'perfil_id' => Perfil::ADMINISTRADOR,
    ]);
    $delegado = Usuario::factory()->create([
        'lotacao_id' => $lotacao->id,
        'perfil_id' => Perfil::ADMINISTRADOR,
        'perfil_concedido_por' => $delegante->id,
    ]);

    expect((new DelegacaoPolicy())->delete($this->usuario, $delegado))->toBeFalse();
});

test('usuário não pode remover delegação de usuário de outra lotação', function () {
    $lotacao_a = Lotacao::factory()->create();
    $lotacao_b = Lotacao::factory()->create();

    $this->usuario->lotacao_id = $lotacao_a->id;
    $this->usuario->perfil_id = Perfil::ADMINISTRADOR;
    $this->usuario->save();

    $delegante = Usuario::factory()->create([
        'lotacao_id' => $lotacao_b->id,
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
    ]);
    $delegado = Usuario::factory()->create([
        'lotacao_id' => $lotacao_b->id,
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
        'perfil_concedido_por' => $delegante->id,
    ]);

    expect((new DelegacaoPolicy())->delete($this->usuario, $delegado))->toBeFalse();
});

// Caminho feliz
test('usuário pode listar as deleções de sua lotação se tiver permissão', function () {
    concederPermissao(Permissao::DelegacaoViewAny->value);

    expect((new DelegacaoPolicy())->viewAny($this->usuario))->toBeTrue();
});

test('usuário pode delegar perfil dentro da mesma lotação se o perfil do delegado for menor na aplicação e se tiver permissão', function () {
    $lotacao = Lotacao::factory()->create();

    $this->usuario->lotacao_id = $lotacao->id;
    $this->usuario->perfil_id = Perfil::GERENTE_NEGOCIO;
    $this->usuario->save();

    concederPermissao(Permissao::DelegacaoCreate->value);

    $delegado = Usuario::factory()->create([
        'lotacao_id' => $lotacao->id,
        'perfil_id' => Perfil::PADRAO,
    ]);

    expect((new DelegacaoPolicy())->create($this->usuario, $delegado))->toBeTrue();
});

test('usuário pode remover delegação de usuário da mesma lotação, com perfil igual ou inferior, mesmo que a delegação tenha sido feita por outro', function () {
    $lotacao = Lotacao::factory()->create();

    $this->usuario->lotacao_id = $lotacao->id;
    $this->usuario->perfil_id = Perfil::GERENTE_NEGOCIO;
    $this->usuario->save();

    $delegante = Usuario::factory()->create([
        'lotacao_id' => $lotacao->id,
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
    ]);

    $delegado_1 = Usuario::factory()->create([
        'lotacao_id' => $lotacao->id,
        'perfil_id' => Perfil::GERENTE_NEGOCIO,
        'perfil_concedido_por' => $delegante->id,
    ]);

    $delegado_2 = Usuario::factory()->create([
        'lotacao_id' => $lotacao->id,
        'perfil_id' => Perfil::OBSERVADOR,
        'perfil_concedido_por' => $delegante->id,
    ]);

    expect((new DelegacaoPolicy())->delete($this->usuario, $delegado_1))->toBeTrue()
    ->and((new DelegacaoPolicy())->delete($this->usuario, $delegado_2))->toBeTrue();
});
