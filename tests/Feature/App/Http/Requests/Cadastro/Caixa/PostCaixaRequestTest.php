<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\Caixa\PostCaixaRequest;
use App\Models\Caixa;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->request = new PostCaixaRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

test('na atualização, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    $caixa = Caixa::factory()->create();

    login();

    // como há uma caixa no request, será uma atualização
    $this->request->caixa = $caixa;

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request para a criação do registro', function () {
    $this->request->prateleira_id = 10;
    $this->request->ano = 2010;
    $this->request->guarda_permanente = true;
    $this->request->complemento = 'foo';
    $this->request->localidade_criadora_id = 20;

    $this->assertExactValidationRules([
        'prateleira_id' => [
            'bail',
            'required',
            'integer',
            'exists:prateleiras,id',
        ],

        'localidade_criadora_id' => [
            'bail',
            'required',
            'integer',
            'exists:localidades,id',
        ],

        'numero' => [
            'bail',
            'required',
            'integer',
            'min:1',
            'unique:caixas,numero,null,id,ano,2010,guarda_permanente,1,complemento,foo,prateleira_id,10,localidade_criadora_id,20',
        ],

        'ano' => [
            'bail',
            'required',
            'integer',
            'between:1900,' . now()->format('Y'),
        ],

        'guarda_permanente' => [
            'boolean',
        ],

        'complemento' => [
            'bail',
            'nullable',
            'string',
            'between:1,50',
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
    $caixa = Caixa::factory()->create();

    // como há uma caixa no request, será uma atualização
    $this->request->caixa = $caixa;

    $this->assertExactValidationRules([
        'prateleira_id' => [
            'bail',
            'nullable',
            'integer',
            'exists:prateleiras,id',
        ],

        'localidade_criadora_id' => [
            'bail',
            'required',
            'integer',
            'exists:localidades,id',
        ],

        'numero' => [
            'bail',
            'required',
            'integer',
            'min:1',
            "unique:caixas,numero,{$caixa->id},id,ano,{$caixa->ano},guarda_permanente,{$caixa->guarda_permanente},complemento,{$caixa->complemento},prateleira_id,{$caixa->prateleira_id},localidade_criadora_id,{$caixa->localidade_criadora_id}",
        ],

        'ano' => [
            'bail',
            'required',
            'integer',
            'between:1900,' . now()->format('Y'),
        ],

        'guarda_permanente' => [
            'boolean',
        ],

        'complemento' => [
            'bail',
            'nullable',
            'string',
            'between:1,50',
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
        'prateleira_id' => __('Prateleira'),
        'localidade_criadora_id' => __('Localidade criadora'),
        'numero' => __('Número'),
        'ano' => __('Ano'),
        'guarda_permanente' => __('Guarda Permanente'),
        'complemento' => __('Complemento'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::CAIXA_CREATE);

    expect($this->request->authorize())->toBeTrue();
});

test('na atualização, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::CAIXA_UPDATE);

    $caixa = Caixa::factory()->create();
    // como há uma caixa no request, será uma atualização
    $this->request->caixa = $caixa;

    expect($this->request->authorize())->toBeTrue();
});
