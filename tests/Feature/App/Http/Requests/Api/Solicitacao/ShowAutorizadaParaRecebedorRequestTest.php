<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Api\Solicitacao\ShowAutorizadaParaRecebedorRequest;
use App\Models\Permissao;
use App\Rules\NumeroProcessoCNJ;
use App\Rules\ProcessoDisponivel;
use App\Rules\RecebedorHabilitado;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new ShowAutorizadaParaRecebedorRequest();
});

// Caminho feliz
test('usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

test('rules estão definidas no form request para a exibição do processo disponível para solicitação', function () {
    $this->assertExactValidationRules([
        'recebedor' => [
            'bail',
            'required',
            'string',
            'between:1,20',
            Rule::exists('usuarios', 'username'),
            new RecebedorHabilitado(),
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'recebedor' => __('Recebedor'),
    ], $this->request->attributes());
});

test('usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::SOLICITACAO_UPDATE);

    expect($this->request->authorize())->toBeTrue();
});
