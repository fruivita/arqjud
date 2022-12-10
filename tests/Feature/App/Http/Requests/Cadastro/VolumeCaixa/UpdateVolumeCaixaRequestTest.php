<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\VolumeCaixa\UpdateVolumeCaixaRequest;
use App\Models\Permissao;
use App\Models\VolumeCaixa;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new UpdateVolumeCaixaRequest();
    $this->volume_caixa = VolumeCaixa::factory()->create();

    $this->request->volume_caixa = $this->volume_caixa;
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
            'between:1,9999',
            Rule::unique('volumes_caixa', 'numero')
                ->where('caixa_id', $this->volume_caixa->caixa_id)
                ->ignore($this->volume_caixa),
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

test('na atualização, usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::VOLUME_CAIXA_UPDATE);

    expect($this->request->authorize())->toBeTrue();
});
