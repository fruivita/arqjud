<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\Andar\PostAndarRequest;
use App\Models\Andar;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->request = new PostAndarRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

test('na atualização, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    $andar = Andar::factory()->create();

    login();

    // como há um andar no request, será uma atualização
    $this->request->andar = $andar;

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request para a criação do registro', function () {
    $this->request->predio_id = 10;

    $this->assertExactValidationRules([
        'predio_id' => [
            'bail',
            'required',
            'integer',
            'exists:predios,id',
        ],

        'numero' => [
            'bail',
            'required',
            'integer',
            'between:-100,300',
            'unique:andares,numero,null,id,predio_id,10',
        ],

        'apelido' => [
            'bail',
            'nullable',
            'string',
            'between:1,100',
            'unique:andares,apelido,null,id,predio_id,10',
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
    $andar = Andar::factory()->create();

    // como há um andar no request, será uma atualização
    $this->request->andar = $andar;

    $this->assertExactValidationRules([
        'predio_id' => [
            'bail',
            'nullable',
            'integer',
            'exists:predios,id',
        ],

        'numero' => [
            'bail',
            'required',
            'integer',
            'between:-100,300',
            "unique:andares,numero,{$andar->id},id,predio_id,{$andar->predio_id}",
        ],

        'apelido' => [
            'bail',
            'nullable',
            'string',
            'between:1,100',
            "unique:andares,apelido,{$andar->id},id,predio_id,{$andar->predio_id}",
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
        'predio_id' => __('Prédio'),
        'numero' => __('Número'),
        'apelido' => __('Apelido'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::ANDAR_CREATE);

    expect($this->request->authorize())->toBeTrue();
});

test('na atualização, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::ANDAR_UPDATE);

    $andar = Andar::factory()->create();
    // como há um andar no request, será uma atualização
    $this->request->andar = $andar;

    expect($this->request->authorize())->toBeTrue();
});
