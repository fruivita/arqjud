<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Api\Solicitacao\ShowSolicitanteRequest;
use App\Models\Permissao;
use App\Models\Usuario;
use App\Rules\UsuarioHabilitado;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new ShowSolicitanteRequest();
});

afterEach(fn () => logout());

// Caminho feliz
test('usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    expect($this->request->authorize())->toBeFalse();
});

test('rules estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'solicitante' => [
            'bail',
            'required',
            'string',
            'between:1,20',
            Rule::exists('usuarios', 'matricula'),
            new UsuarioHabilitado(),
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'solicitante' => __('Solicitante'),
    ], $this->request->attributes());
});

test('usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    concederPermissao(Permissao::SOLICITACAO_CREATE);

    expect($this->request->authorize())->toBeTrue();
});
