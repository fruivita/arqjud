<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Caixa;
use App\Models\Processo;
use App\Models\VolumeCaixa;
use App\Pipes\Caixa\SetGPProcessos;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('atualiza o status de guarda permanente de todos os processos da caixa', function (bool $gp) {
    $caixa = Caixa::factory()
        ->has(VolumeCaixa::factory(2)->hasProcessos(3, ['guarda_permanente' => !$gp]), 'volumes')
        ->create();

    Processo::factory(2)->create(['guarda_permanente' => !$gp]); // não serão afetados

    $caixa->guarda_permanente = $gp;

    Pipeline::make()
        ->send($caixa)
        ->through([SetGPProcessos::class])
        ->thenReturn();

    expect(Processo::where('guarda_permanente', $gp)->count())->toBe(6)
        ->and(Processo::where('guarda_permanente', !$gp)->count())->toBe(2);
})->with([
    true,
    false,
]);
