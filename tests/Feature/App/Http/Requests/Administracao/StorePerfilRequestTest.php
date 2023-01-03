<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Administracao\StorePerfilRequest;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new StorePerfilRequest();
});

afterEach(function () {
    logout();
});

// Autorização
test('usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'nome' => [
            'bail',
            'required',
            'string',
            'between:1,50',
            Rule::unique('perfis', 'nome'),
        ],
        'slug' => [
            'bail',
            'required',
            'string',
            'between:1,50',
            Rule::unique('perfis', 'slug'),
        ],
        'poder' => [
            'bail',
            'required',
            'integer',
            'between:1001,9998',
            Rule::unique('perfis', 'poder'),
        ],
        'descricao' => [
            'bail',
            'nullable',
            'string',
            'between:1,255',
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'nome' => __('Nome'),
        'slug' => __('Slug'),
        'poder' => __('Poder'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::PERFIL_CREATE);

    expect($this->request->authorize())->toBeTrue();
});
