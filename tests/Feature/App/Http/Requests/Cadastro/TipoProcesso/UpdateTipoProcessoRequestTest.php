<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\TipoProcesso\UpdateTipoProcessoRequest;
use App\Models\TipoProcesso;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new UpdateTipoProcessoRequest();
    $this->tipo_processo = TipoProcesso::factory()->create();

    $this->request->tipo_processo = $this->tipo_processo;
});

// Autorização
test('na atualização, usuário sem autorização não cria o request', function () {
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
            Rule::unique('tipos_processo', 'nome')
                ->ignore($this->tipo_processo),
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

test('na atualização, usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::TIPO_PROCESSO_UPDATE);

    expect($this->request->authorize())->toBeTrue();
});
