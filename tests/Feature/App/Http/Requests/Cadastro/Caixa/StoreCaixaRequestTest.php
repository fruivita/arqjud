<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\Caixa\StoreCaixaRequest;
use App\Models\Localidade;
use App\Models\Permissao;
use App\Models\Prateleira;
use App\Models\TipoProcesso;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new StoreCaixaRequest();
});

// Autorização
test('na criação, usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request', function () {
    $prateleira = Prateleira::factory()->create();
    $localidade = Localidade::factory()->create();
    $tipo_processo = TipoProcesso::factory()->create();

    $this->request->prateleira = $prateleira;
    $this->request->ano = 2010;
    $this->request->guarda_permanente = true;
    $this->request->complemento = 'foo';
    $this->request->localidade_criadora_id = $localidade->id;
    $this->request->tipo_processo_id = $tipo_processo->id;

    $this->assertExactValidationRules([
        'localidade_criadora_id' => [
            'bail',
            'required',
            'integer',
            Rule::exists('localidades', 'id'),
        ],

        'tipo_processo_id' => [
            'bail',
            'required',
            'integer',
            Rule::exists('tipos_processo', 'id'),
        ],

        'numero' => [
            'bail',
            'required',
            'integer',
            'min:1',
            Rule::unique('caixas', 'numero')
                ->where('ano', 2010)
                ->where('guarda_permanente', 1)
                ->where('localidade_criadora_id', $localidade->id)
                ->where('tipo_processo_id', $tipo_processo->id)
                ->where('prateleira_id', $prateleira->id)
                ->where('complemento', 'foo'),
        ],

        'ano' => [
            'bail',
            'required',
            'integer',
            'between:1900,' . now()->format('Y'),
        ],

        'guarda_permanente' => [
            'boolean',
        ],

        'complemento' => [
            'bail',
            'nullable',
            'string',
            'between:1,50',
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
        'localidade_criadora_id' => __('Localidade criadora'),
        'tipo_processo_id' => __('Tipo de processo'),
        'numero' => __('Número'),
        'ano' => __('Ano'),
        'guarda_permanente' => __('Guarda Permanente'),
        'complemento' => __('Complemento'),
        'descricao' => __('Descrição'),
    ], $this->request->attributes());
});

test('na criação, usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::CAIXA_CREATE);

    expect($this->request->authorize())->toBeTrue();
});
