<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Lotacao;
use App\Models\Processo;
use App\Models\Usuario;
use App\Pipes\Solicitacao\SolicitarProcesso;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\PestPluginTestTime\testTime;

// Caminho feliz
test('pipe SolicitarProcesso cria as solicitações de processos no status solicitadas', function () {
    testTime()->freeze();

    $processo = Processo::factory()->create();
    $processos = Processo::factory(2)->create();

    $solicitacao = new \stdClass();
    $solicitacao->processos = $processos->pluck('numero')->transform('apenasNumeros')->toArray();
    $solicitacao->solicitante = Usuario::factory()->create();
    $solicitacao->destino = Lotacao::factory()->create();
    $solicitacao->solicitada_em = now();

    Pipeline::make()
        ->withTransaction()
        ->send($solicitacao)
        ->through([SolicitarProcesso::class])
        ->thenReturn();

    $this
        ->assertDatabaseCount('solicitacoes', 2)
        ->assertDatabaseHas('solicitacoes', [
            'processo_id' => $processos->get(0)->id,
            'solicitante_id' => $solicitacao->solicitante->id,
            'recebedor_id' => null,
            'remetente_id' => null,
            'rearquivador_id' => null,
            'destino_id' => $solicitacao->destino->id,
            'guia_id' => null,
            'solicitada_em' => $solicitacao->solicitada_em,
            'entregue_em' => null,
            'devolvida_em' => null,
            'por_guia' => false,
            'descricao' => null,
            'created_at' => $solicitacao->solicitada_em,
            'updated_at' => $solicitacao->solicitada_em,
        ])
        ->assertDatabaseHas('solicitacoes', [
            'processo_id' => $processos->get(1)->id,
            'solicitante_id' => $solicitacao->solicitante->id,
            'recebedor_id' => null,
            'remetente_id' => null,
            'rearquivador_id' => null,
            'destino_id' => $solicitacao->destino->id,
            'guia_id' => null,
            'solicitada_em' => $solicitacao->solicitada_em,
            'entregue_em' => null,
            'devolvida_em' => null,
            'por_guia' => false,
            'descricao' => null,
            'created_at' => $solicitacao->solicitada_em,
            'updated_at' => $solicitacao->solicitada_em,
        ])
        ->assertDatabaseMissing('solicitacoes', [
            'processo_id' => $processo->id,
        ]);
});
