<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Api\Solicitacao\ShowProcessoDisponivelRequest;
use App\Models\Permissao;
use App\Models\Usuario;
use App\Rules\NumeroProcessoCNJ;
use App\Rules\ProcessoDisponivel;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new ShowProcessoDisponivelRequest();
});

afterEach(fn () => logout());

// Caminho feliz
test('usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    expect($this->request->authorize())->toBeFalse();
});

test('rules estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'numero' => [
            'bail',
            'required',
            'string',
            'regex:/^\d+$/',
            'max:25',
            new NumeroProcessoCNJ(),
            Rule::exists('processos', 'numero'),
            new ProcessoDisponivel(),
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'numero' => __('Processo'),
    ], $this->request->attributes());
});

test('usuário autorizado pode criar o request', function (string $permissao) {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    concederPermissao($permissao);

    expect($this->request->authorize())->toBeTrue();
})->with([
    Permissao::SOLICITACAO_EXTERNA_CREATE,
    Permissao::SOLICITACAO_CREATE,
]);
