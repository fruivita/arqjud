<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportarEstruturaCorporativa;
use App\Models\Lotacao;
use App\Models\FuncaoConfianca;
use App\Models\Cargo;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;

// Caminho feliz
test('o job importa a estrutura coporativa', function () {
    $this->seed(PerfilSeeder::class);

    ImportarEstruturaCorporativa::dispatchSync();

    expect(Cargo::count())->toBe(3)
    ->and(FuncaoConfianca::count())->toBe(3)
    ->and(Lotacao::count())->toBe(5)
    ->and(Usuario::count())->toBe(5);
});
