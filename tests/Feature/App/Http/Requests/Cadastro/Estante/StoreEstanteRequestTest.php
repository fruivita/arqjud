<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\Estante\StoreEstanteRequest;
use App\Models\Permissao;
use App\Models\Sala;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new StoreEstanteRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request', function () {
    $sala = Sala::factory()->create();

    $this->request->sala = $sala;

    $this->assertExactValidationRules([
        'numero' => [
            'bail',
            'required',
            'string',
            'between:1,50',
            Rule::unique('estantes', 'numero')
                ->where('sala_id', $sala->id),
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
        'numero' => __('Número'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::ESTANTE_CREATE);

    expect($this->request->authorize())->toBeTrue();
});
