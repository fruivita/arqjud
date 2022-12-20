<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Guia;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Pipes\Solicitacao\EfetivarEntrega;
use Database\Seeders\PerfilSeeder;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\PestPluginTestTime\testTime;

// Caminho feliz
test('pipe EfetivarEntrega cria as solicitações de processos no status solicitadas', function () {
    $this->seed([PerfilSeeder::class]);

    login();
    testTime()->freeze();

    $recebedor = Usuario::factory()->create();
    $remetente = Usuario::factory()->create();
    $solicitacoes = Solicitacao::factory(2)->solicitada()->create(['lotacao_destinataria_id' => $recebedor->lotacao_id]);
    $guia = Guia::factory()->create();

    $entrega = new \stdClass();
    $entrega->recebedor = $recebedor;
    $entrega->remetente = $remetente;
    $entrega->guia = $guia;
    $entrega->por_guia = 1;
    $entrega->solicitacoes = $solicitacoes->pluck('id');

    Pipeline::make()
        ->withTransaction()
        ->send($entrega)
        ->through([EfetivarEntrega::class])
        ->thenReturn();

    $this
        ->assertDatabaseCount('solicitacoes', 2)
        ->assertDatabaseHas('solicitacoes', [
            'processo_id' => $solicitacoes->get(0)->processo_id,
            'solicitante_id' => $solicitacoes->get(0)->solicitante_id,
            'recebedor_id' => $recebedor->id,
            'remetente_id' => $remetente->id,
            'rearquivador_id' => null,
            'lotacao_destinataria_id' => $solicitacoes->get(0)->lotacao_destinataria_id,
            'guia_id' => $guia->id,
            'entregue_em' => $guia->gerada_em,
            'devolvida_em' => null,
            'por_guia' => $entrega->por_guia,
            'descricao' => $solicitacoes->get(0)->descricao,
        ])
        ->assertDatabaseHas('solicitacoes', [
            'processo_id' => $solicitacoes->get(1)->processo_id,
            'solicitante_id' => $solicitacoes->get(1)->solicitante_id,
            'recebedor_id' => $recebedor->id,
            'remetente_id' => $remetente->id,
            'rearquivador_id' => null,
            'lotacao_destinataria_id' => $solicitacoes->get(1)->lotacao_destinataria_id,
            'guia_id' => $guia->id,
            'entregue_em' => $guia->gerada_em,
            'devolvida_em' => null,
            'por_guia' => $entrega->por_guia,
            'descricao' => $solicitacoes->get(1)->descricao,
        ]);
});
