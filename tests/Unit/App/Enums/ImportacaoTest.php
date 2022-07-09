<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Importacao;

// Caminho feliz
test('enum Importacao corretamente definido', function () {
    expect(Importacao::Corporativo->value)->toBe('corporativo');
});

test('nomes definidos para o enum Importacao', function () {
    expect(Importacao::Corporativo->nome())->toBe(__('Estrutura corporativa'));
});

test('queues defineda para o enum Importacao', function () {
    expect(Importacao::Corporativo->queue())->toBe('corporativo');
});

test('valores definidos para o enum AcaoCheckbox', function () {
    expect(Importacao::valores()->toArray())->toBe(['corporativo']);
});
