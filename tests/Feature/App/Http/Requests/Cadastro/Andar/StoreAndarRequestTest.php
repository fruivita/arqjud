<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\Andar\StoreAndarRequest;
use App\Models\Permissao;
use App\Models\Predio;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new StoreAndarRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request para a criação do registro', function () {
    $predio = Predio::factory()->create();
    $this->request->predio = $predio;

    $this->assertExactValidationRules([
        'numero' => [
            'bail',
            'required',
            'integer',
            'between:-100,300',
            Rule::unique('andares', 'numero')
                ->where('predio_id', $predio->id),
        ],

        'apelido' => [
            'bail',
            'nullable',
            'string',
            'between:1,100',
            Rule::unique('andares', 'apelido')
                ->where('predio_id', $predio->id),
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
        'apelido' => __('Apelido'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::ANDAR_CREATE);

    expect($this->request->authorize())->toBeTrue();
});
