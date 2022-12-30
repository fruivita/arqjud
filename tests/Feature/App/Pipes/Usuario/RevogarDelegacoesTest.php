<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Perfil;
use App\Models\Usuario;
use App\Pipes\Usuario\RevogarDelegacoes;
use Database\Seeders\PerfilSeeder;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('altera o perfil do usuário', function () {
    $this->seed([PerfilSeeder::class]);

    $perfis = Perfil::all();

    $delegante = Usuario::factory()->create();

    $delegado_1 = Usuario::factory()->create([
        'perfil_id' => $perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO)->id,
        'perfil_concedido_por' => $delegante->id,
        'antigo_perfil_id' => $perfis->firstWhere('slug', Perfil::OBSERVADOR)->id,
    ]);

    $delegado_2 = Usuario::factory()->create([
        'perfil_id' => $perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO)->id,
        'perfil_concedido_por' => $delegante->id,
        'antigo_perfil_id' => $perfis->firstWhere('slug', Perfil::PADRAO)->id,
    ]);

    $delegado_3 = Usuario::factory()->create([
        'perfil_id' => $perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO)->id,
        'perfil_concedido_por' => Usuario::factory()->create()->id,
        'antigo_perfil_id' => $perfis->firstWhere('slug', Perfil::PADRAO)->id,
    ]);

    Pipeline::make()
        ->send($delegante)
        ->through([RevogarDelegacoes::class])
        ->thenReturn();

    $delegado_1->refresh();
    $delegado_2->refresh();

    expect($delegado_1->perfil_id)->toBe($perfis->firstWhere('slug', Perfil::OBSERVADOR)->id)
        ->and($delegado_1->perfil_concedido_por)->toBeNull()
        ->and($delegado_1->antigo_perfil_id)->toBeNull()
        ->and($delegado_2->perfil_id)->toBe($perfis->firstWhere('slug', Perfil::PADRAO)->id)
        ->and($delegado_2->perfil_concedido_por)->toBeNull()
        ->and($delegado_2->antigo_perfil_id)->toBeNull()
        ->and($delegado_3->perfil_id)->toBe($perfis->firstWhere('slug', Perfil::GERENTE_NEGOCIO)->id)
        ->and($delegado_3->perfil_concedido_por)->not->toBeNull()
        ->and($delegado_3->antigo_perfil_id)->not->toBeNull();
});
