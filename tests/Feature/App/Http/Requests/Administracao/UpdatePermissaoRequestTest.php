<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Administracao\UpdatePermissaoRequest;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new UpdatePermissaoRequest();
    $this->permissao = Permissao::factory()->create();

    $this->request->permissao = $this->permissao;
});

afterEach(fn () => logout());

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
            Rule::unique('permissoes', 'nome')
                ->ignore($this->permissao),
        ],
        'descricao' => [
            'bail',
            'nullable',
            'string',
            'between:1,255',
        ],
        'perfil_id' => [
            'bail',
            'nullable',
            'integer',
            Rule::exists('perfis', 'id'),
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'nome' => __('Nome'),
        'descricao' => __('Descrição'),
        'perfil_id' => __('Perfil'),
    ], $this->request->attributes());
});

test('usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::PERMISSAO_UPDATE);

    expect($this->request->authorize())->toBeTrue();
});
