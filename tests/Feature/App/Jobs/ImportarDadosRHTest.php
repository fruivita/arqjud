<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportarDadosRH;
use App\Models\Cargo;
use App\Models\FuncaoConfianca;
use App\Models\Lotacao;
use App\Models\Usuario;

// Caminho feliz
test('job ImportarDadosRH importa os dados do arquivo corporativo', function () {
    ImportarDadosRH::dispatchSync();

    expect(Cargo::count())->toBe(3)
        ->and(FuncaoConfianca::count())->toBe(3)
        ->and(Lotacao::count())->toBe(5)
        ->and(Usuario::count())->toBe(5);
});
