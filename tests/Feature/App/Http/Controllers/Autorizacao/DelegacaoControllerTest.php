<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Autorizacao\DelegacaoController;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use function Pest\Laravel\patch;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->perfis = Perfil::all();

    $this->lotacao = Lotacao::factory()->create();

    $this->delegante = Usuario::factory()
        ->for($this->lotacao, 'lotacao')
        ->for($this->perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO), 'perfil')
        ->create();
    Auth::login($this->delegante);

    $this->delegado = Usuario::factory()
        ->for($this->lotacao, 'lotacao')
        ->for($this->perfis->firstWhere('slug', Perfil::OPERADOR), 'perfil')
        ->create();
});

afterEach(function () {
    logout();
});

// Autorização
test('delegante sem permissão não consegue delegar perfil', function () {
    patch(route('autorizacao.delegacao.store', $this->delegado))->assertForbidden();
});

test('não é possível remover delegação inexistente', function () {
    patch(route('autorizacao.delegacao.destroy', $this->delegado))->assertForbidden();
});

// // Caminho feliz
test('delegante delega sua permissão ao delegado', function () {
    concederPermissao(Permissao::DELEGACAO_CREATE);

    patch(route('autorizacao.delegacao.store', $this->delegado))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $this->delegado->refresh();

    expect($this->delegado->perfil_id)->toBe($this->delegante->perfil_id)
        ->and($this->delegado->antigo_perfil_id)->toBe($this->perfis->firstWhere('slug', Perfil::OPERADOR)->id)
        ->and($this->delegado->perfil_concedido_por)->toBe($this->delegante->id);
});

test('remove a delegação do delegado', function () {
    concederPermissao(Permissao::DELEGACAO_DELETE);

    $this->delegado->antigo_perfil_id = $this->delegado->perfil_id;
    $this->delegado->perfil_id = $this->delegante->perfil_id;
    $this->delegado->perfil_concedido_por = $this->delegante->id;
    $this->delegado->save();

    expect($this->delegado->perfilDelegado())->toBeTrue();

    patch(route('autorizacao.delegacao.destroy', $this->delegado))
        ->assertRedirect()
        ->assertSessionHas('feedback.sucesso');

    $this->delegado->refresh();

    expect($this->delegado->perfil_id)->toBe($this->perfis->firstWhere('slug', Perfil::OPERADOR)->id)
        ->and($this->delegado->perfil_concedido_por)->toBeNull()
        ->and($this->delegado->antigo_perfil_id)->toBeNull();
});

test('DelegacaoController usa trait', function () {
    expect(
        collect(class_uses(DelegacaoController::class))
            ->has([
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
