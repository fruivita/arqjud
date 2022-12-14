<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue;

// Caminho feliz
test('Queue enum corretamente definidas', function () {
    expect(Queue::Imediata->value)->toBe('imediata')
        ->and(Queue::Alta->value)->toBe('alta')
        ->and(Queue::Media->value)->toBe('media')
        ->and(Queue::Baixa->value)->toBe('baixa');
});
