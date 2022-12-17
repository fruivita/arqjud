<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Api\Caixa\ShowCaixaRequest;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new ShowCaixaRequest();
});

// Caminho feliz
test('request dispensa autorização específica', function () {
    expect($this->request->authorize())->toBeTrue();
});

test('rules estão definidas no form request para a exibição da caixa e seus volumes', function ($complemento) {
    $this->request->localidade_criadora_id = 10;
    $this->request->ano = 2000;
    $this->request->guarda_permanente = 1;
    $this->request->complemento = $complemento;
    $this->request->numero = '111';

    $this->assertExactValidationRules([
        'localidade_criadora_id' => [
            'bail',
            'required',
            'integer',
            Rule::exists('localidades', 'id'),
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
        'numero' => [
            'bail',
            'required',
            'integer',
            'min:1',
            Rule::exists('caixas', 'numero')
                ->where('ano', $this->request->ano)
                ->where('guarda_permanente', $this->request->guarda_permanente)
                ->when(
                    $this->request->complemento,
                    function ($query, $complemento) {
                        return $query->where('complemento', $complemento);
                    },
                    function ($query) {
                        return $query->whereNull('complemento');
                    }
                )
                ->where('localidade_criadora_id', $this->request->localidade_criadora_id),
        ],
    ], $this->request->rules());
})->with(['foo', null]);

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'localidade_criadora_id' => __('Localidade criadora'),
        'ano' => __('Ano'),
        'guarda_permanente' => __('Guarda Permanente'),
        'complemento' => __('Complemento'),
        'numero' => __('Número'),
    ], $this->request->attributes());
});
