<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Events\ProcessoSolicitadoPeloUsuario;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use function Spatie\PestPluginTestTime\testTime;

// Caminho feliz
test('evento ProcessoSolicitadoPeloUsuario inicializa todas as propriedades', function () {
    $this->seed([PerfilSeeder::class]);

    testTime()->freeze();

    $solicitacao = new \stdClass();
    $solicitacao->processos = ['1111', '2222'];
    $solicitacao->solicitante = Usuario::factory()->create();

    $event = new ProcessoSolicitadoPeloUsuario($solicitacao);

    expect($event->processos)->toBe(['1111', '2222'])
        ->and($event->solicitante->is($solicitacao->solicitante))->toBeTrue()
        ->and($event->solicitada_em->toString())->toBe(now()->toString());
});
