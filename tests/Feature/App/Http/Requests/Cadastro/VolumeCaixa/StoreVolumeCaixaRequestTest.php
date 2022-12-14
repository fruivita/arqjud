<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\VolumeCaixa\StoreVolumeCaixaRequest;
use App\Models\Caixa;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new StoreVolumeCaixaRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request para a criação do registro', function () {
    $caixa = Caixa::factory()->create();

    $this->request->caixa = $caixa;

    $this->assertExactValidationRules([
        'numero' => [
            'bail',
            'required',
            'integer',
            'between:1,9999',
            Rule::unique('volumes_caixa', 'numero')
                ->where('caixa_id', $caixa->id),
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

    concederPermissao(Permissao::VOLUME_CAIXA_CREATE);

    expect($this->request->authorize())->toBeTrue();
});
