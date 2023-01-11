<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Atendimento\StoreEntregarProcessoRequest;
use App\Models\Permissao;
use App\Models\Usuario;
use App\Rules\PasswordValido;
use App\Rules\SolicitacaoEntregavel;
use App\Rules\UsuarioHabilitado;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new StoreEntregarProcessoRequest();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request', function () {
    $this->request->por_guia = rand(0, 1) ? true : false;
    $this->request->recebedor = 'foo';

    $this->assertExactValidationRules([
        'recebedor' => [
            'bail',
            'required',
            'string',
            'between:1,20',
            Rule::exists('usuarios', 'matricula'),
            new UsuarioHabilitado(),
        ],

        'por_guia' => ['boolean'],

        'password' => [
            'bail',
            Rule::excludeIf($this->request->por_guia === true),
            'required',
            'string',
            'max:50',
            new PasswordValido($this->request->recebedor),
        ],

        'solicitacoes.*' => [
            'bail',
            'required',
            'integer',
            Rule::exists('solicitacoes', 'id'),
            new SolicitacaoEntregavel(),
        ],

        'email_terceiros.*' => ['nullable', 'email:strict'],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'recebedor' => __('Recebedor'),
        'por_guia' => __('Entrega por guia'),
        'password' => __('Senha'),
        'solicitacoes.*' => __('Solicitação'),
        'email_terceiros.*' => __('Email'),
    ], $this->request->attributes());
});

test('usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    expect($this->request->authorize())->toBeTrue();
});
