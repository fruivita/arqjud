<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Queue as EQueue;
use App\Jobs\ImportarDadosRH;
use Illuminate\Support\Facades\Queue;

// Caminho feliz
test('comando rh:importar envia o job ImportarDadosRH para a queue', function () {
    Queue::fake();

    $this
        ->artisan('rh:importar')
        ->assertSuccessful();

    Queue::assertPushedOn(EQueue::Alta->value, ImportarDadosRH::class);
});
