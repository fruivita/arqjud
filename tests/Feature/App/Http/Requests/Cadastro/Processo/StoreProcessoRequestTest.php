<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\Processo\StoreProcessoRequest;
use App\Models\Caixa;
use App\Models\Permissao;
use App\Models\Usuario;
use App\Rules\NumeroProcesso;
use App\Rules\NumeroProcessoCNJ;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new StoreProcessoRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request', function () {
    $caixa = Caixa::factory()->create();

    $this->request->caixa = $caixa;

    $this->assertExactValidationRules([
        'processo_pai_numero' => [
            'bail',
            'nullable',
            'string',
            'regex:/^\d+$/',
            'max:25',
            new NumeroProcessoCNJ(),
            Rule::exists('processos', 'numero'),
        ],

        'numero' => [
            'bail',
            'required',
            'string',
            'regex:/^\d+$/',
            'max:25',
            new NumeroProcessoCNJ(),
            Rule::unique('processos', 'numero'),
        ],

        'numero_antigo' => [
            'bail',
            'nullable',
            'string',
            new NumeroProcesso(),
            Rule::unique('processos', 'numero_antigo'),
        ],

        'arquivado_em' => [
            'bail',
            'required',
            'date_format:d-m-Y',
            'after_or_equal:01-01-1900',
            'before_or_equal:' . now()->format('d-m-Y'),
        ],

        'vol_caixa_inicial' => [
            'bail',
            'required',
            'integer',
            'between:1,9999',
        ],

        'vol_caixa_final' => [
            'bail',
            'required',
            'integer',
            'gte:vol_caixa_inicial',
            'max:9999',
        ],

        'qtd_volumes' => [
            'bail',
            'required',
            'integer',
            'between:1,9999',
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
        'processo_pai_numero' => __('Processo pai'),
        'numero' => __('Processo'),
        'numero_antigo' => __('Número antigo do processo'),
        'arquivado_em' => __('Data de arquivamento'),
        'vol_caixa_inicial' => __('Vol inicial da caixa'),
        'vol_caixa_final' => __('Vol final da caixa'),
        'qtd_volumes' => __('Qtd volumes'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    Auth::login(Usuario::factory()->create());

    concederPermissao(Permissao::PROCESSO_CREATE);

    expect($this->request->authorize())->toBeTrue();
});
