<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->perfis = Perfil::all();

    $this->lotacao = Lotacao::factory()->create();

    // Usuário com perfil superior
    $this->delegante = Usuario::factory()
        ->for($this->lotacao, 'lotacao')
        ->for($this->perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO), 'perfil')
        ->create();

    Auth::login($this->delegante);

    // Usuário com perfil inferior
    $this->delegado = Usuario::factory()
        ->for($this->lotacao, 'lotacao')
        ->for($this->perfis->firstWhere('slug', Perfil::OPERADOR), 'perfil')
        ->create();
});

afterEach(function () {
    logout();
});

// Proibido
test('delegante sem lotação não pode delegar seu perfil independente de permissão', function () {
    concederPermissao(Permissao::DELEGACAO_CREATE);

    $this->delegante->lotacao()->dissociate()->save();

    expect($this->delegante->can(Policy::DelegacaoCreate->value, $this->delegado))->toBeFalse();
});

test('delegante não pode delegar seu perfil para usuário sem lotação independente de permissão', function () {
    concederPermissao(Permissao::DELEGACAO_CREATE);

    $this->delegado->lotacao()->dissociate()->save();

    expect($this->delegante->can(Policy::DelegacaoCreate->value, $this->delegado))->toBeFalse();
});

test('delegante não pode delegar seu perfil para usuário de outra lotação independente de permissão', function () {
    concederPermissao(Permissao::DELEGACAO_CREATE);

    $this->delegado->lotacao_id = Lotacao::factory()->create()->id;
    $this->delegado->save();

    expect($this->delegante->can(Policy::DelegacaoCreate->value, $this->delegado))->toBeFalse();
});

test('delegante não pode delegar seu perfil para usuário de perfil igual ou superior independente de permissão', function () {
    concederPermissao(Permissao::DELEGACAO_CREATE);

    $this->delegado->perfil_id = $this->perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id;
    $this->delegado->save();

    expect($this->delegante->can(Policy::DelegacaoCreate->value, $this->delegado))->toBeFalse();
});

test('delegante com perfil delegado não pode delegar seu perfil independente de permissão', function () {
    concederPermissao(Permissao::DELEGACAO_CREATE);

    $this->delegante->antigo_perfil_id = Perfil::factory()->create()->id;
    $this->delegante->perfil_concedido_por = Usuario::factory()->create()->id;
    $this->delegante->perfil_id = $this->perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id;
    $this->delegante->save();

    expect($this->delegante->can(Policy::DelegacaoCreate->value, $this->delegado))->toBeFalse();
});

test('delegante não pode delegar seu perfil para usuário com perfil delegado independente de permissão', function () {
    concederPermissao(Permissao::DELEGACAO_CREATE);

    $this->delegado->antigo_perfil_id = Perfil::factory()->create()->id;
    $this->delegado->perfil_concedido_por = Usuario::factory()->create()->id;
    $this->delegado->perfil_id = $this->perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id;
    $this->delegado->save();

    expect($this->delegante->can(Policy::DelegacaoCreate->value, $this->delegado))->toBeFalse();
});

test('usuário sem permissão não pode delegar seu perfil', function () {
    expect($this->delegante->can(Policy::DelegacaoCreate->value, $this->delegado))->toBeFalse();
});

test('não se pode remover delegação inexistente', function () {
    expect($this->delegante->can(Policy::DelegacaoDelete->value, $this->delegado))->toBeFalse();
});

test('usuário não pode revogar delegação de outra lotação', function () {
    $this->delegado->antigo_perfil_id = Perfil::factory()->create()->id;
    $this->delegado->perfil_concedido_por = Usuario::factory()->for($this->perfis->firstWhere('slug', Perfil::OPERADOR), 'perfil')->create()->id;
    $this->delegado->lotacao_id = Lotacao::factory()->create()->id;
    $this->delegado->save();

    expect($this->delegante->can(Policy::DelegacaoDelete->value, $this->delegado))->toBeFalse();
});

test('usuário não pode revogar delegação de perfil superior', function () {
    $this->delegado->antigo_perfil_id = Perfil::factory()->create()->id;
    $this->delegado->perfil_concedido_por = Usuario::factory()->create()->id;
    $this->delegado->perfil_id = $this->perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id;
    $this->delegado->save();

    expect($this->delegante->can(Policy::DelegacaoDelete->value, $this->delegado))->toBeFalse();
});

// Caminho feliz
test('usuário pode delegar seu perfil para usuário da mesma lotação e com perfil inferior, desde que com permissão e perfis originais', function () {
    concederPermissao(Permissao::DELEGACAO_CREATE);

    expect($this->delegante->can(Policy::DelegacaoCreate->value, $this->delegado))->toBeTrue();
});

test('usuário pode revogar suas delegações independente de permissão', function () {
    $this->delegado->antigo_perfil_id = Perfil::factory()->create()->id;
    $this->delegado->perfil_concedido_por = $this->delegante->id;
    $this->delegado->save();

    expect($this->delegante->can(Policy::DelegacaoDelete->value, $this->delegado))->toBeTrue();
});

test('usuário pode revogar qualquer delegação de sua lotação, desde que seu perfil seja superior', function () {
    $this->delegado->antigo_perfil_id = Perfil::factory()->create()->id;
    $this->delegado->perfil_concedido_por = Usuario::factory()->for($this->perfis->firstWhere('slug', Perfil::OPERADOR), 'perfil')->create()->id;
    $this->delegado->save();

    expect($this->delegante->can(Policy::DelegacaoDelete->value, $this->delegado))->toBeTrue();
});

test('usuário com permissão pode revogar qualquer delegação de qualquer lotação', function () {
    concederPermissao(Permissao::DELEGACAO_DELETE);

    $this->delegado->antigo_perfil_id = Perfil::factory()->create()->id;
    $this->delegado->perfil_concedido_por = Usuario::factory()->create()->id;
    $this->delegado->perfil_id = $this->perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id;
    $this->delegado->save();

    expect($this->delegante->can(Policy::DelegacaoDelete->value, $this->delegado))->toBeTrue();
});
