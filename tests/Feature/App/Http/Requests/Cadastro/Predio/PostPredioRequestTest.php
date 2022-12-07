<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Models\Permissao;
use App\Http\Requests\Cadastro\Predio\PostPredioRequest;
use App\Models\Predio;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->request = new PostPredioRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

test('na atualização, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    $predio = Predio::factory()->create();

    login();

    // como há um prédio no request, será uma atualização
    $this->request->predio = $predio;

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request para a criação do registro', function () {
    $this->request->localidade_id = 10;

    $this->assertExactValidationRules([
        'localidade_id' => [
            'bail',
            'required',
            'integer',
            'exists:localidades,id',
        ],

        'nome' => [
            'bail',
            'required',
            'string',
            'between:1,100',
            'unique:predios,nome,null,id,localidade_id,10',
        ],

        'descricao' => [
            'bail',
            'nullable',
            'string',
            'between:1,255',
        ],
    ], $this->request->rules());
});

test('rules estão definidas no form request para a atualização do registro', function () {
    $predio = Predio::factory()->create();

    // como há um prédio no request, será uma atualização
    $this->request->predio = $predio;

    $this->assertExactValidationRules([
        'localidade_id' => [
            'bail',
            'nullable',
            'integer',
            'exists:localidades,id',
        ],

        'nome' => [
            'bail',
            'required',
            'string',
            'between:1,100',
            "unique:predios,nome,{$predio->id},id,localidade_id,{$predio->localidade_id}",
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
        'localidade_id' => __('Localidade'),
        'nome' => __('Nome'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::PREDIO_CREATE);

    expect($this->request->authorize())->toBeTrue();
});

test('na atualização, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::PREDIO_UPDATE);

    $predio = Predio::factory()->create();
    // como há um prédio no request, será uma atualização
    $this->request->predio = $predio;

    expect($this->request->authorize())->toBeTrue();
});
