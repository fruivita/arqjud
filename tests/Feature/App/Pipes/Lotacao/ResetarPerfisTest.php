<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Usuario;
use App\Pipes\Lotacao\ResetarPerfis;
use Database\Seeders\PerfilSeeder;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('reseta para o perfil padrão todos os usuarios da lotação, exceto o perfil administrador, se a lotação for não administrável', function () {
    $this->seed([PerfilSeeder::class]);
    $this->perfis = Perfil::all();
    $lotacao = Lotacao::factory()->create(['administravel' => false]);

    $adm = Usuario::factory()->create([
        'lotacao_id' => $lotacao->id,
        'perfil_id' => $this->perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id,
    ]);

    $nao_adm = Usuario::factory()->create(['lotacao_id' => $lotacao->id]);

    $outra_lotacao = Usuario::factory()->create();

    Pipeline::make()
        ->send($lotacao)
        ->through([ResetarPerfis::class])
        ->thenReturn();

    $adm->refresh();
    $nao_adm->refresh();
    $outra_lotacao->refresh();

    expect($adm->perfil->id)->toBe($this->perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id)
        ->and($nao_adm->perfil->id)->toBe($this->perfis->firstWhere('slug', Perfil::PADRAO)->id)
        ->and($outra_lotacao->perfil->id)->not->toBe($this->perfis->firstWhere('slug', Perfil::PADRAO)->id);
});

test('não reseta para o perfil padrão se a lotação for administrável', function () {
    $this->seed([PerfilSeeder::class]);
    $this->perfis = Perfil::all();
    $lotacao = Lotacao::factory()->create(['administravel' => true]);

    $adm = Usuario::factory()->create([
        'lotacao_id' => $lotacao->id,
        'perfil_id' => $this->perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id,
    ]);

    $nao_adm = Usuario::factory()->create(['lotacao_id' => $lotacao->id]);

    $outra_lotacao = Usuario::factory()->create();

    Pipeline::make()
        ->send($lotacao)
        ->through([ResetarPerfis::class])
        ->thenReturn();

    $adm->refresh();
    $nao_adm->refresh();
    $outra_lotacao->refresh();

    expect($adm->perfil->id)->toBe($this->perfis->firstWhere('slug', Perfil::ADMINISTRADOR)->id)
        ->and($nao_adm->perfil->id)->not->toBe($this->perfis->firstWhere('slug', Perfil::PADRAO)->id)
        ->and($outra_lotacao->perfil->id)->not->toBe($this->perfis->firstWhere('slug', Perfil::PADRAO)->id);
});
