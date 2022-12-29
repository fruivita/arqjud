<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Api\Solicitacao\ShowAutorizadaParaRecebedorRequest;
use App\Models\Permissao;
use App\Models\Usuario;
use App\Rules\UsuarioHabilitado;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new ShowAutorizadaParaRecebedorRequest();
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    expect($this->request->authorize())->toBeFalse();
});

test('rules estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'recebedor' => [
            'bail',
            'required',
            'string',
            'between:1,20',
            Rule::exists('usuarios', 'username'),
            new UsuarioHabilitado(),
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'recebedor' => __('Recebedor'),
    ], $this->request->attributes());
});

test('usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    expect($this->request->authorize())->toBeTrue();
});
