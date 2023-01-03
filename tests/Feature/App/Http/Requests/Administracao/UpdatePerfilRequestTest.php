<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Administracao\UpdatePerfilRequest;
use App\Models\Perfil;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new UpdatePerfilRequest();
    $this->perfil = Perfil::factory()->create();

    $this->request->perfil = $this->perfil;
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
            Rule::unique('perfis', 'nome')
                ->ignore($this->perfil),
        ],
        'slug' => [
            'bail',
            'required',
            'string',
            'between:1,50',
            Rule::unique('perfis', 'slug')
                ->ignore($this->perfil),
        ],
        'descricao' => [
            'bail',
            'nullable',
            'string',
            'between:1,255',
        ],
        'permissao_id' => [
            'bail',
            'nullable',
            'integer',
            Rule::exists('permissoes', 'id'),
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'nome' => __('Nome'),
        'slug' => __('Slug'),
        'descricao' => __('Descrição'),
        'permissao_id' => __('Permissão'),
    ], $this->request->attributes());
});

test('usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::PERFIL_UPDATE);

    expect($this->request->authorize())->toBeTrue();
});
