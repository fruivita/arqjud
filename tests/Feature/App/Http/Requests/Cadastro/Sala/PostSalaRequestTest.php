<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Models\Permissao;
use App\Http\Requests\Cadastro\Sala\PostSalaRequest;
use App\Models\Sala;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->request = new PostSalaRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

test('na atualização, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    $sala = Sala::factory()->create();

    login();

    // como há uma sala no request, será uma atualização
    $this->request->sala = $sala;

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request para a criação do registro', function () {
    $this->request->andar_id = 10;

    $this->assertExactValidationRules([
        'andar_id' => [
            'bail',
            'required',
            'integer',
            'exists:andares,id',
        ],

        'numero' => [
            'bail',
            'required',
            'string',
            'between:1,50',
            'unique:salas,numero,null,id,andar_id,10',
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
    $sala = Sala::factory()->create();

    // como há uma sala no request, será uma atualização
    $this->request->sala = $sala;

    $this->assertExactValidationRules([
        'andar_id' => [
            'bail',
            'nullable',
            'integer',
            'exists:andares,id',
        ],

        'numero' => [
            'bail',
            'required',
            'string',
            'between:1,50',
            "unique:salas,numero,{$sala->id},id,andar_id,{$sala->andar_id}",
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
        'andar_id' => __('Andar'),
        'numero' => __('Número'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::SALA_CREATE);

    expect($this->request->authorize())->toBeTrue();
});

test('na atualização, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::SALA_UPDATE);

    $sala = Sala::factory()->create();
    // como há uma sala no request, será uma atualização
    $this->request->sala = $sala;

    expect($this->request->authorize())->toBeTrue();
});
