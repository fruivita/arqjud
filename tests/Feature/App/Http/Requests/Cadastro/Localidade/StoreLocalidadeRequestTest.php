<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\Localidade\StoreLocalidadeRequest;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new StoreLocalidadeRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o request', function () {
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
            'between:1,100',
            Rule::unique('localidades', 'nome'),
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
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::LOCALIDADE_CREATE);

    expect($this->request->authorize())->toBeTrue();
});
