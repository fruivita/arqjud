<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Solicitacao\StoreSolicitacaoRequest;
use App\Models\Permissao;
use App\Rules\NumeroProcessoCNJ;
use App\Rules\ProcessoDisponivel;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new StoreSolicitacaoRequest();
});

// Autorização
test('usuário sem autorização não cria o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'processos.*.numero' => [
            'bail',
            'required',
            'string',
            'max:25',
            new NumeroProcessoCNJ(),
            Rule::exists('processos', 'numero'),
            new ProcessoDisponivel(),
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'processos.*.numero' => __('Número do processo'),
    ], $this->request->attributes());
});

test('usuário autorizado pode criar o resquest', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::SOLICITACAO_EXTERNA_CREATE);

    expect($this->request->authorize())->toBeTrue();
});
