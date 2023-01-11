<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Guia;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Pipes\Solicitacao\GerarGuiaRemessa;
use Database\Seeders\PerfilSeeder;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\PestPluginTestTime\testTime;

// Caminho feliz
test('pipe GerarGuiaRemessa cria a guia de remessa e a inclui no pipeable', function () {
    $this->seed([PerfilSeeder::class]);

    login();
    testTime()->freeze();

    $recebedor = Usuario::factory()->create();
    $solicitacoes = Solicitacao::factory(2)->solicitada()->create(['destino_id' => $recebedor->lotacao_id]);

    $entrega = new \stdClass();
    $entrega->recebedor = $recebedor->matricula;
    $entrega->solicitacoes = $solicitacoes->pluck('id');

    $salvo = Pipeline::make()
        ->withTransaction()
        ->send($entrega)
        ->through([GerarGuiaRemessa::class])
        ->thenReturn();

    expect(Guia::count())->toBe(1)
        ->and($salvo->guia)->toBeInstanceOf(Guia::class);
});
