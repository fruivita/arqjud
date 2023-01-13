<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Atendimento\StoreSolicitacaoRequest;
use App\Models\Permissao;
use App\Models\Usuario;
use App\Rules\NumeroProcessoCNJ;
use App\Rules\ProcessoDisponivel;
use App\Rules\UsuarioHabilitado;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new StoreSolicitacaoRequest();
});

// Autorização
test('usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'solicitante_id' => [
            'bail',
            'required',
            'integer',
            Rule::exists('usuarios', 'id'),
            new UsuarioHabilitado(),
        ],
        'destino_id' => [
            'bail',
            'required',
            'integer',
            'min:1',
            Rule::exists('lotacoes', 'id'),
        ],
        'processos.*.numero' => [
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
        'solicitante_id' => __('Solicitante'),
        'destino_id' => __('Destino'),
        'processos.*.numero' => __('Processo'),
    ], $this->request->attributes());
});

test('usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    concederPermissao(Permissao::SOLICITACAO_CREATE);

    expect($this->request->authorize())->toBeTrue();
});
