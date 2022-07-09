<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportarEstruturaCorporativa;
use Illuminate\Support\Facades\Bus;

// Caminho feliz
test('comando importar:corporativo dispara o job "ImportarArquivoCorporativo"', function () {
    Bus::fake();

    $this
        ->artisan('importar:corporativo')
        ->assertSuccessful();

    Bus::assertDispatched(ImportarEstruturaCorporativa::class);
});
