<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\VolumeCaixa\PostVolumeCaixaRequest;
use App\Models\Permissao;
use App\Models\VolumeCaixa;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->request = new PostVolumeCaixaRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

test('na atualização, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    $volume_caixa = VolumeCaixa::factory()->create();

    login();

    // como há um volume da caixa no request, será uma atualização
    $this->request->volume_caixa = $volume_caixa;

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request para a criação do registro', function () {
    $this->request->caixa_id = 10;

    $this->assertExactValidationRules([
        'caixa_id' => [
            'bail',
            'required',
            'integer',
            'exists:caixas,id',
        ],

        'numero' => [
            'bail',
            'required',
            'integer',
            'between:1,9999',
            'unique:volumes_caixa,numero,null,id,caixa_id,10',
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
    $volume_caixa = VolumeCaixa::factory()->create();

    // como há um volume da caixa no request, será uma atualização
    $this->request->volume_caixa = $volume_caixa;

    $this->assertExactValidationRules([
        'caixa_id' => [
            'bail',
            'nullable',
            'integer',
            'exists:caixas,id',
        ],

        'numero' => [
            'bail',
            'required',
            'integer',
            'between:1,9999',
            "unique:volumes_caixa,numero,{$volume_caixa->id},id,caixa_id,{$volume_caixa->caixa_id}",
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
        'caixa_id' => __('Caixa'),
        'numero' => __('Número'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::VOLUME_CAIXA_CREATE);

    expect($this->request->authorize())->toBeTrue();
});

test('na atualização, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::VOLUME_CAIXA_UPDATE);

    $volume_caixa = VolumeCaixa::factory()->create();
    // como há um volume da caixa no request, será uma atualização
    $this->request->volume_caixa = $volume_caixa;

    expect($this->request->authorize())->toBeTrue();
});
