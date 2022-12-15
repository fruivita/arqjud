<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Events\ProcessoSolicitadoPeloUsuario;
use App\Models\Processo;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Pipes\Solicitacao\CriarSolicitacao;
use Illuminate\Support\Facades\Event;
use MichaelRubel\EnhancedPipeline\Pipeline;

use function Spatie\PestPluginTestTime\testTime;

// Caminho feliz
test('pipe CriarSolicitacao cria as solicitações de processos no status solicitadas', function () {
    testTime()->freeze();

    $processo = Processo::factory()->create();
    $processos = Processo::factory(2)->create();

    $solicitacao = new stdClass();
    $solicitacao->processos = $processos->pluck('numero')->transform('apenasNumeros')->toArray();
    $solicitacao->solicitante = Usuario::factory()->create();

    Pipeline::make()
        ->withTransaction()
        ->send($solicitacao)
        ->through([CriarSolicitacao::class])
        ->thenReturn();

    $this
        ->assertDatabaseCount('solicitacoes', 2)
        ->assertDatabaseHas('solicitacoes', [
            'processo_id' => $processos->get(0)->id,
            'solicitante_id' => $solicitacao->solicitante->id,
            'recebedor_id' => null,
            'remetente_id' => null,
            'rearquivador_id' => null,
            'lotacao_destinataria_id' => $solicitacao->solicitante->lotacao_id,
            'guia_id' => null,
            'solicitada_em' => now(),
            'entregue_em' => null,
            'devolvida_em' => null,
            'por_guia' => false,
            'descricao' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ])
        ->assertDatabaseHas('solicitacoes', [
            'processo_id' => $processos->get(1)->id,
            'solicitante_id' => $solicitacao->solicitante->id,
            'recebedor_id' => null,
            'remetente_id' => null,
            'rearquivador_id' => null,
            'lotacao_destinataria_id' => $solicitacao->solicitante->lotacao_id,
            'guia_id' => null,
            'solicitada_em' => now(),
            'entregue_em' => null,
            'devolvida_em' => null,
            'por_guia' => false,
            'descricao' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ])
        ->assertDatabaseMissing('solicitacoes', [
            'processo_id' => $processo->id,
        ]);
})->only();
