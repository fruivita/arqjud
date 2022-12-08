<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */
beforeEach(function () {
    $this->classe = new class
    {
        use App\Traits\ComFeedback;
    };
});

// Caminho feliz
test('feedback de falha na operação', function () {
    expect($this->classe->feedback(false))->toBe(['feedback' => ['erro' => __('Falha na execução do comando!')]]);
});

test('feedback de falha na operação com mensagem específica', function () {
    expect($this->classe->feedback(false, 'foo'))->toBe(['feedback' => ['erro' => 'foo']]);
});

test('feedback de operação bem sucedida', function () {
    expect($this->classe->feedback(true))->toBe(['feedback' => ['sucesso' => __('Comando executado com sucesso!')]]);
});

test('feedback de operação bem sucedida com mensagem específica', function () {
    expect($this->classe->feedback(true, 'foo'))->toBe(['feedback' => ['sucesso' => 'foo']]);
});
