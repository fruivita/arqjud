<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportarDadosRH;
use App\Pipes\Importacao\Importar;
use Illuminate\Support\Facades\Bus;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('sem importação válida, não há importação', function () {
    $stdClass = new \stdClass;
    $stdClass->importacoes = ['foo'];

    $importacao = Pipeline::make()
        ->send($stdClass)
        ->through([Importar::class])
        ->thenReturn();

    expect($importacao->importado)->toBeFalse();
});

test('com importação válida, dispara o job ImportarDadosRH', function () {
    $stdClass = new \stdClass;
    $stdClass->importacoes = ['rh'];

    Bus::fake();

    $importacao = Pipeline::make()
        ->send($stdClass)
        ->through([Importar::class])
        ->thenReturn();

    expect($importacao->importado)->toBeTrue();

    Bus::assertNotDispatchedSync(ImportarDadosRH::class);
    Bus::assertDispatchedTimes(ImportarDadosRH::class, 1);
});
