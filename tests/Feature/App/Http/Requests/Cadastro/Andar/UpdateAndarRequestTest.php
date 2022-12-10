<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\Andar\UpdateAndarRequest;
use App\Models\Andar;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new UpdateAndarRequest();
    $this->andar = Andar::factory()->create();

    $this->request->andar = $this->andar;
});

// Autorização
test('na atualização, usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request para a atualização do registro', function () {
    $this->assertExactValidationRules([
        'numero' => [
            'bail',
            'required',
            'integer',
            'between:-100,300',
            Rule::unique('andares', 'numero')
                ->where('predio_id', $this->andar->predio_id)
                ->ignore($this->andar),
        ],

        'apelido' => [
            'bail',
            'nullable',
            'string',
            'between:1,100',
            Rule::unique('andares', 'apelido')
                ->where('predio_id', $this->andar->predio_id)
                ->ignore($this->andar),
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

test('na atualização, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::ANDAR_UPDATE);

    expect($this->request->authorize())->toBeTrue();
});
