<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Movimentacao\StoreMoveProcessoEntreCaixaRequest;
use App\Models\Permissao;
use App\Rules\NumeroProcessoCNJ;
use App\Rules\ProcessoMovimentavel;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new StoreMoveProcessoEntreCaixaRequest();
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
        'volume_id' => [
            'bail',
            'required',
            'integer',
            Rule::exists('volumes_caixa', 'id'),
        ],

        'processos.*.numero' => [
            'bail',
            'required',
            'string',
            'regex:/\d+/',
            'max:25',
            new NumeroProcessoCNJ(),
            Rule::exists('processos', 'numero'),
            new ProcessoMovimentavel(),
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'volume_id' => __('Volume de destino'),
        'processos.*.numero' => __('Número do processo'),
    ], $this->request->attributes());
});

test('usuário autorizado pode criar o resquest', function (string $permissao) {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao($permissao);

    expect($this->request->authorize())->toBeTrue();
})->with([
    Permissao::MOVER_PROCESSO_CREATE,
]);
