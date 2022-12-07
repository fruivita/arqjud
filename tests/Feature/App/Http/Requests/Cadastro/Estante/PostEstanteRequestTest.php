<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Models\Permissao;
use App\Http\Requests\Cadastro\Estante\PostEstanteRequest;
use App\Models\Estante;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->request = new PostEstanteRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

test('na atualização, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    $estante = Estante::factory()->create();

    login();

    // como há uma estante no request, será uma atualização
    $this->request->estante = $estante;

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request para a criação do registro', function () {
    $this->request->sala_id = 10;

    $this->assertExactValidationRules([
        'sala_id' => [
            'bail',
            'required',
            'integer',
            'exists:salas,id',
        ],

        'numero' => [
            'bail',
            'required',
            'string',
            'between:1,50',
            'unique:estantes,numero,null,id,sala_id,10',
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
    $estante = Estante::factory()->create();

    // como há uma estante no request, será uma atualização
    $this->request->estante = $estante;

    $this->assertExactValidationRules([
        'sala_id' => [
            'bail',
            'nullable',
            'integer',
            'exists:salas,id',
        ],

        'numero' => [
            'bail',
            'required',
            'string',
            'between:1,50',
            "unique:estantes,numero,{$estante->id},id,sala_id,{$estante->sala_id}",
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
        'sala_id' => __('Sala'),
        'numero' => __('Número'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::ESTANTE_CREATE);

    expect($this->request->authorize())->toBeTrue();
});

test('na atualização, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::ESTANTE_UPDATE);

    $estante = Estante::factory()->create();
    // como há uma estante no request, será uma atualização
    $this->request->estante = $estante;

    expect($this->request->authorize())->toBeTrue();
});
