<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\AcaoCheckbox;

// Caminho feliz
test('enum AcaoCheckbox corretamente definido', function () {
    expect(AcaoCheckbox::SelecionarTodos->value)->toBe('selecionar-todos')
    ->and(AcaoCheckbox::DesmarcarTodos->value)->toBe('desmarcar-todos')
    ->and(AcaoCheckbox::SelecionarTodosNaPagina->value)->toBe('selecionar-todos-na-pagina')
    ->and(AcaoCheckbox::DesmarcarTodosNaPagina->value)->toBe('desmarcar-todos-na-pagina');
});

test('nomes definidos para o enum AcaoCheckbox', function () {
    expect(AcaoCheckbox::SelecionarTodos->nome())->toBe(__('Marcar todos'))
    ->and(AcaoCheckbox::DesmarcarTodos->nome())->toBe(__('Desmarcar todos'))
    ->and(AcaoCheckbox::SelecionarTodosNaPagina->nome())->toBe(__('Marcar todos na página'))
    ->and(AcaoCheckbox::DesmarcarTodosNaPagina->nome())->toBe(__('Desmarcar todos na página'));
});

test('valores definidos para o enum AcaoCheckbox', function () {
    expect(AcaoCheckbox::valores()->toArray())->toBe(['selecionar-todos', 'desmarcar-todos', 'selecionar-todos-na-pagina', 'desmarcar-todos-na-pagina']);
});
