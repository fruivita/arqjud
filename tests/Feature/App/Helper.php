<?php

/**
 * @see https://pestphp.com/docs/
 */

// Caminho feliz
test('helper ascOrDesc() retorna a ordenação a que deve ser utilizada sendo desc a ordenação padrão', function () {
    expect(ascOrDesc())->toBe('desc')
        ->and(ascOrDesc('foo'))->toBe('desc')
        ->and(ascOrDesc('desc'))->toBe('desc')
        ->and(ascOrDesc('asc'))->toBe('asc')
        ->and(ascOrDesc('AsC'))->toBe('asc');
});
