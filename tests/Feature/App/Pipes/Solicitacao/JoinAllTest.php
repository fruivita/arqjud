<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Lotacao;
use App\Models\Processo;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Pipes\Solicitacao\JoinAll;
use MichaelRubel\EnhancedPipeline\Pipeline;

// Caminho feliz
test('join da tabela processos', function () {
    $processo = Processo::factory()
        ->hasSolicitacoes(Solicitacao::factory())
        ->create();

    $solicitacao = Pipeline::make()
        ->send(Solicitacao::query())
        ->through([JoinAll::class])
        ->thenReturn()
        ->pluck('processos.numero');

    expect($solicitacao->first())->toBe(apenasNumeros($processo->numero));
});

test('join da tabela usuários solicitantes', function () {
    $usuario = Usuario::factory()
        ->hasSolicitacoesSolicitadas(Solicitacao::factory())
        ->create();

    $solicitacao = Pipeline::make()
        ->send(Solicitacao::query())
        ->through([JoinAll::class])
        ->thenReturn()
        ->pluck('solicitantes.matricula');

    expect($solicitacao->first())->toBe($usuario->matricula);
});

test('join da tabela usuários recebedores', function () {
    $usuario = Usuario::factory()
        ->hasSolicitacoesRecebidas(Solicitacao::factory())
        ->create();

    $solicitacao = Pipeline::make()
        ->send(Solicitacao::query())
        ->through([JoinAll::class])
        ->thenReturn()
        ->pluck('recebedores.matricula');

    expect($solicitacao->first())->toBe($usuario->matricula);
});

test('join da tabela usuários remetentes', function () {
    $usuario = Usuario::factory()
        ->hasSolicitacoesRemetidas(Solicitacao::factory())
        ->create();

    $solicitacao = Pipeline::make()
        ->send(Solicitacao::query())
        ->through([JoinAll::class])
        ->thenReturn()
        ->pluck('remetentes.matricula');

    expect($solicitacao->first())->toBe($usuario->matricula);
});

test('join da tabela usuários rearquivadores', function () {
    $usuario = Usuario::factory()
        ->hasSolicitacoesRearquivadas(Solicitacao::factory())
        ->create();

    $solicitacao = Pipeline::make()
        ->send(Solicitacao::query())
        ->through([JoinAll::class])
        ->thenReturn()
        ->pluck('rearquivadores.matricula');

    expect($solicitacao->first())->toBe($usuario->matricula);
});

test('join da tabela lotações (destinos)', function () {
    $lotacao = Lotacao::factory()
        ->hasSolicitacoes(Solicitacao::factory())
        ->create();

    $solicitacao = Pipeline::make()
        ->send(Solicitacao::query())
        ->through([JoinAll::class])
        ->thenReturn()
        ->pluck('destinos.sigla');

    expect($solicitacao->first())->toBe($lotacao->sigla);
});
