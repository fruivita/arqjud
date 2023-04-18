<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Cargo;

test('Cargo usa trait', function () {
    expect(
        collect(class_uses(Cargo::class))
            ->has([
                \App\Models\Trait\Auditavel::class,
            ])
    )->toBeTrue();
});
