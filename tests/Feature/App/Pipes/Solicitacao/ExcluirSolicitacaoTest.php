<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Solicitacao;
use App\Pipes\Solicitacao\ExcluirSolicitacao;
use Database\Seeders\PerfilSeeder;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\PestPluginTestTime\testTime;

// Caminho feliz
test('pipe ExcluirSolicitacao exclui a solicitação informada mas mantém suas propriedades para uso no próximo pipe', function () {
    $this->seed([PerfilSeeder::class]);

    login();
    testTime()->freeze();

    $solicitacao = Solicitacao::factory()->solicitada()->create();

    $std = new \stdClass();
    $std->model = $solicitacao;

    expect(Solicitacao::count())->toBe(1);

    $new_std = Pipeline::make()
        ->withTransaction()
        ->send($std)
        ->through([ExcluirSolicitacao::class])
        ->thenReturn();

    expect(Solicitacao::count())->toBe(0)
        ->and($new_std->processo)->toBe($solicitacao->processo->numero)
        ->and($new_std->solicitante->is($solicitacao->solicitante))->toBeTrue()
        ->and($new_std->destino->is($solicitacao->destino))->toBeTrue()
        ->and($new_std->solicitada_em->is($solicitacao->solicitada_em))->toBeTrue()
        ->and($new_std->operador->is(auth()->user()))->toBeTrue()
        ->and($new_std->cancelada_em->is(now()))->toBeTrue();
});
