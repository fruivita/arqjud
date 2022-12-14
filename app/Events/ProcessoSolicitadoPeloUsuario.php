<?php

namespace App\Events;

use App\Models\Usuario;
use FruiVita\Corporativo\Models\Lotacao;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * Evento disparado face a solicitação de processos feita pelo próprio usuário.
 *
 * @link https://laravel.com/docs/9.x/events
 */
class ProcessoSolicitadoPeloUsuario
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Número dos processos solicitados
     *
     * @var string[]
     */
    public $processos;

    /**
     * Usuário solicitante.
     *
     * @var \App\Models\Usuario
     */
    public Usuario $solicitante;

    /**
     * Data e hora da solicitação
     *
     * @var \Illuminate\Support\Carbon
     */
    public Carbon $solicitada_em;

    /**
     * Create a new event instance.
     *
     * A lotação destinatária será a lotação do próprio usuário.
     *
     * @param  \stdClass  $solicitacao
     * @return void
     */
    public function __construct(\stdClass $solicitacao)
    {
        $this->processos = $solicitacao->processos;
        $this->solicitante = $solicitacao->solicitante;
        $this->solicitada_em = now();
    }
}
