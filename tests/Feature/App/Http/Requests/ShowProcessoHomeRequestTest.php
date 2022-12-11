<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Requests\ShowProcessoHomeRequest;
use App\Rules\MultiColumnExists;
use App\Rules\NumeroProcesso;

beforeEach(function () {
    $this->request = new ShowProcessoHomeRequest();
});

// Autorização
test('request dispensa autorização específica', function () {
    expect($this->request->authorize())->toBeTrue();
});

// Caminho feliz
test('rules estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'termo' => [
            'bail',
            'nullable',
            'string',
            'max:25',
            new NumeroProcesso(),
            new MultiColumnExists('processos', ['numero', 'numero_antigo']),
        ],
    ], $this->request->rules());
});

test('attributes estão definidas no form request', function () {
    $this->assertExactValidationRules([
        'termo' => __('Número do processo'),
    ], $this->request->attributes());
});
