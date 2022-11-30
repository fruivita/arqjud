<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\View\Components\Translations;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('componente renderiza respeitando o snapshot', function () {
    $html = (string) $this->component(Translations::class);

    assertMatchesSnapshot($html);
});
