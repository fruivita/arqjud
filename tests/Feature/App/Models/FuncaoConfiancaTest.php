<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\FuncaoConfianca;

test('FuncaoConfianca usa trait', function () {
    expect(
        collect(class_uses(FuncaoConfianca::class))
            ->has([
                \App\Models\Trait\Auditavel::class,
            ])
    )->toBeTrue();
});
