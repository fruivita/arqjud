<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Autorizacao\UpdateUsuarioRequest;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new UpdateUsuarioRequest();

    $this->request->usuario = Usuario::factory()->create();
});

// Autorização
test('usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    expect($this->request->authorize())->toBeFalse();
});

test('usuário mesmo com autorização, não cria o request para si mesmo', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    concederPermissao(Permissao::USUARIO_UPDATE);

    $this->request->usuario = Auth::user();

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'perfil_id' => [
            'bail',
            'required',
            'integer',
            Rule::exists('perfis', 'id'),
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'perfil_id' => __('Perfil'),
    ], $this->request->attributes());
});

test('usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    concederPermissao(Permissao::USUARIO_UPDATE);

    expect($this->request->authorize())->toBeTrue();
});
