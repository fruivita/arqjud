<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Cadastro\Caixa\UpdateCaixaRequest;
use App\Models\Caixa;
use App\Models\Localidade;
use App\Models\Permissao;
use App\Models\TipoProcesso;
use Database\Seeders\PerfilSeeder;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new UpdateCaixaRequest();
    $this->caixa = Caixa::factory()->create();

    $this->request->caixa = $this->caixa;
});

// Autorização
test('na atualização, usuário sem autorização não cria o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    expect($this->request->authorize())->toBeFalse();
});

// Caminho feliz
test('rules estão definidas no form request', function () {
    $localidade = Localidade::factory()->create();
    $tipo_processo = TipoProcesso::factory()->create();

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
                ->where('prateleira_id', $this->caixa->prateleira_id)
                ->where('complemento', 'foo')
                ->ignore($this->caixa),
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

test('na atualização, usuário autorizado pode criar o request', function () {
    $this->seed([PerfilSeeder::class]);

    login();

    concederPermissao(Permissao::CAIXA_UPDATE);

    expect($this->request->authorize())->toBeTrue();
});
