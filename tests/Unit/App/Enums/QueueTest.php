<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue;

// Caminho feliz
test('Queue enum corretamente definidos', function () {
    expect(Queue::Corporativo->value)->toBe('corporativo');
});
