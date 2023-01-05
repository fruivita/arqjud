<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Cargo;
use App\Models\FuncaoConfianca;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Usuario;
use App\Pipes\Usuario\JoinAll;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('join da tabela lotacoes', function () {
    $lotacao = Lotacao::factory()->create();
    Usuario::factory()->for($lotacao, 'lotacao')->create();

    $usuario = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class])
        ->thenReturn()
        ->pluck('lotacoes.nome');

    expect($usuario->first())->toBe($lotacao->nome);
});

test('join da tabela cargos', function () {
    $cargo = Cargo::factory()->create();
    Usuario::factory()->for($cargo, 'cargo')->create();

    $usuario = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class])
        ->thenReturn()
        ->pluck('cargos.nome');

    expect($usuario->first())->toBe($cargo->nome);
});

test('join da tabela funcoes_confianca', function () {
    $funcao = FuncaoConfianca::factory()->create();
    Usuario::factory()->for($funcao, 'funcaoConfianca')->create();

    $usuario = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class])
        ->thenReturn()
        ->pluck('funcoes_confianca.nome');

    expect($usuario->first())->toBe($funcao->nome);
});

test('join da tabela perfis', function () {
    $perfil = Perfil::factory()
        ->hasUsuarios(Usuario::factory())
        ->create();

    $usuario = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class])
        ->thenReturn()
        ->pluck('perfis.nome');

    expect($usuario->first())->toBe($perfil->nome);
});
