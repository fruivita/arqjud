<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Perfil;
use App\Models\Usuario;
use App\Pipes\Usuario\AlterarPerfil;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use MichaelRubel\EnhancedPipeline\Pipeline;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->perfis = Perfil::all();

    $this->usuario = Usuario::factory()
        ->for($this->perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO), 'perfil')
        ->create();

    Auth::login($this->usuario);
});

afterEach(fn () => logout());

// Exception
test('lança exception caso o perfil do usuário alvo seja maior que o do usuário autenticado', function () {
    $usuario = Usuario::factory()
        ->for($this->perfis->firstWhere('slug', Perfil::ADMINISTRADOR), 'perfil')
        ->create();

    /** @var \RuntimeException */
    $expection = Pipeline::make()
        ->send($usuario)
        ->through([AlterarPerfil::class . ':' . $this->perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO)->id])
        ->onFailure(fn ($data, \Throwable $expection) => $expection)
        ->thenReturn();

    $usuario->refresh();

    expect($usuario->perfil_id)->toBe($this->perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id)
        ->and($expection)->toBeInstanceOf(\RuntimeException::class)
        ->and($expection->getMessage())->toBe(__('Tentativa de alteração de perfil superior'));
});

test('lança exception caso o perfil desejado para o usuário alvo seja maior que o do usuário autenticado', function () {
    $usuario = Usuario::factory()
        ->for($this->perfis->firstWhere('slug', Perfil::OPERADOR), 'perfil')
        ->create();

    /** @var \RuntimeException */
    $expection = Pipeline::make()
        ->send($usuario)
        ->through([AlterarPerfil::class . ':' . $this->perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id])
        ->onFailure(fn ($data, \Throwable $expection) => $expection)
        ->thenReturn();

    $usuario->refresh();

    expect($usuario->perfil_id)->toBe($this->perfis->firstWhere('slug', Perfil::OPERADOR)->id)
        ->and($expection)->toBeInstanceOf(\RuntimeException::class)
        ->and($expection->getMessage())->toBe(__('Tentativa de alteração de perfil superior'));
});

// Caminho feliz
test('altera o perfil do usuário para perfil no máximo igual ao do usuário autenticado', function (string $novo_perfil) {
    $usuario = Usuario::factory()
        ->for($this->perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO), 'perfil')
        ->create();

    Pipeline::make()
        ->send($usuario)
        ->through([AlterarPerfil::class . ':' . $this->perfis->firstWhere('slug', $novo_perfil)->id])
        ->thenReturn();

    $usuario->refresh();

    expect($usuario->perfil_id)->toBe($this->perfis->firstWhere('slug', $novo_perfil)->id);
})->with([
    Perfil::OPERADOR,
    Perfil::OBSERVADOR,
    Perfil::PADRAO,
]);

test('perfil inicial do usuário pode ser nulo', function () {
    $usuario = Usuario::factory()->create(['perfil_id' => null]);

    Pipeline::make()
        ->send($usuario)
        ->through([AlterarPerfil::class . ':' . $this->perfis->firstWhere('slug', Perfil::OPERADOR)->id])
        ->thenReturn();

    $usuario->refresh();

    expect($usuario->perfil_id)->toBe($this->perfis->firstWhere('slug', Perfil::OPERADOR)->id);
});
