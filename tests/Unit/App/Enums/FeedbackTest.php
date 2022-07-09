<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Feedback;

// Caminho feliz
test('enum Feedback corretamente definido', function () {
    expect(Feedback::Sucesso->value)->toBe('sucesso')
    ->and(Feedback::Erro->value)->toBe('erro');
});

test('nomes definidos para o enum Feedback', function () {
    expect(Feedback::Sucesso->nome())->toBe(__('Sucesso!'))
    ->and(Feedback::Erro->nome())->toBe(__('Erro!'));
});

test('icones definidos para o enum Feedback', function () {
    expect(Feedback::Sucesso->icone())->toContain('emoji-smile', '<svg class="icone"', '</svg>')
    ->and(Feedback::Erro->icone())->toContain('emoji-frown', '<svg class="icone"', '</svg>');
});
