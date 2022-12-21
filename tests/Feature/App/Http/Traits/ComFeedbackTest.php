<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */
beforeEach(function () {
    $this->classe = new class
    {
        use App\Http\Traits\ComFeedback;
    };
});

// Caminho feliz
test('feedback de falha na operação', function () {
    expect($this->classe->feedback(false))->toMatchArray(['feedback' => ['erro' => __('Falha na execução do comando!')]]);
});

test('feedback de falha na operação com mensagem específica', function () {
    expect($this->classe->feedback(false, 'foo'))->toMatchArray(['feedback' => ['erro' => 'foo']]);
});

test('feedback de operação bem sucedida', function () {
    expect($this->classe->feedback(true))->toMatchArray(['feedback' => ['sucesso' => __('Comando executado com sucesso!')]]);
});

test('feedback de operação bem sucedida com mensagem específica', function () {
    expect($this->classe->feedback(true, 'foo'))->toMatchArray(['feedback' => ['sucesso' => 'foo']]);
});
