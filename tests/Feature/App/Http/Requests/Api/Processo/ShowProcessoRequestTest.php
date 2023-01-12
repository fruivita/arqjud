<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\Api\Processo\ShowProcessoRequest;
use App\Rules\NumeroProcessoCNJ;
use Illuminate\Validation\Rule;

beforeEach(function () {
    $this->request = new ShowProcessoRequest();
});

// Caminho feliz
test('request dispensa autorização específica', function () {
    expect($this->request->authorize())->toBeTrue();
});

test('rules estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'numero' => [
            'bail',
            'required',
            'string',
            'regex:/\d+/',
            'max:25',
            new NumeroProcessoCNJ(),
            Rule::exists('processos', 'numero'),
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'numero' => __('Número do processo'),
    ], $this->request->attributes());
});
