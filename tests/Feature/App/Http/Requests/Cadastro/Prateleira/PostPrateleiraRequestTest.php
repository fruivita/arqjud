<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\Prateleira\PostPrateleiraRequest;
use App\Models\Permissao;
use App\Models\Prateleira;
use Database\Seeders\PerfilSeeder;

beforeEach(function () {
    $this->request = new PostPrateleiraRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

test('na atualização, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    $prateleira = Prateleira::factory()->create();

    login();

    // como há uma prateleira no request, será uma atualização
    $this->request->prateleira = $prateleira;

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request para a criação do registro', function () {
    $this->request->estante_id = 10;

    $this->assertExactValidationRules([
        'estante_id' => [
            'bail',
            'required',
            'integer',
            'exists:estantes,id',
        ],

        'numero' => [
            'bail',
            'required',
            'string',
            'between:1,50',
            'unique:prateleiras,numero,null,id,estante_id,10',
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
    $prateleira = Prateleira::factory()->create();

    // como há uma prateleira no request, será uma atualização
    $this->request->prateleira = $prateleira;

    $this->assertExactValidationRules([
        'estante_id' => [
            'bail',
            'nullable',
            'integer',
            'exists:estantes,id',
        ],

        'numero' => [
            'bail',
            'required',
            'string',
            'between:1,50',
            "unique:prateleiras,numero,{$prateleira->id},id,estante_id,{$prateleira->estante_id}",
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
        'estante_id' => __('Estante'),
        'numero' => __('Número'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::PRATELEIRA_CREATE);

    expect($this->request->authorize())->toBeTrue();
});

test('na atualização, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::PRATELEIRA_UPDATE);

    $prateleira = Prateleira::factory()->create();
    // como há uma prateleira no request, será uma atualização
    $this->request->prateleira = $prateleira;

    expect($this->request->authorize())->toBeTrue();
});
