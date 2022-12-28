<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Guia;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Pipes\Solicitacao\EfetivarDevolucao;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\PestPluginTestTime\testTime;

// Caminho feliz
test('pipe EfetivarDevolucao atualiza a solicitação para o status devolvida', function () {
    $this->seed([PerfilSeeder::class]);

    login();
    testTime()->freeze();

    $solicitacao = Solicitacao::factory()->entregue()->create();
    $processo = $solicitacao->processo()->first();

    $devolucao = new \stdClass();
    $devolucao->processo = apenasNumeros($processo->numero);

    $pipe = Pipeline::make()
        ->withTransaction()
        ->send($devolucao)
        ->through([EfetivarDevolucao::class])
        ->thenReturn();

    expect($pipe->devolvido_em->toString())->toBe(now()->toString())
        ->and($pipe->solicitante)->toBeInstanceOf(Usuario::class)
        ->and($pipe->solicitante->id)->toBe($solicitacao->solicitante->id);

    $this
        ->assertDatabaseCount('solicitacoes', 1)
        ->assertDatabaseHas('solicitacoes', [
            'processo_id' => $solicitacao->processo_id,
            'solicitante_id' => $solicitacao->solicitante_id,
            'rearquivador_id' => Auth::id(),
            'lotacao_destinataria_id' => $solicitacao->lotacao_destinataria_id,
            'devolvida_em' => now(),
            'descricao' => $solicitacao->descricao,
        ]);
});
