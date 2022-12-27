<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\Predio\UpdatePredioRequest;
use App\Models\Permissao;
use App\Models\Predio;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new UpdatePredioRequest();
    $this->predio = Predio::factory()->create();

    $this->request->predio = $this->predio;
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
            Rule::unique('predios', 'nome')
                ->where('localidade_id', $this->predio->localidade_id)
                ->ignore($this->predio),
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

    concederPermissao(Permissao::PREDIO_UPDATE);

    expect($this->request->authorize())->toBeTrue();
});
