<?php

namespace App\Jobs;

use App\Models\Lotacao;
use App\Models\Usuario;
use App\Notifications\SolicitacaoCancelada;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

/**
 * Notifica o solicitante sobre o cancelamento de sua solicitação de processos.
 *
 * @see https://laravel.com/docs/9.x/queues
 */
class NotificarSolicitanteCancelamento implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de tentativas de execução do job.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Número de segundos de espera antes de tentar executar novamente o job.
     *
     * @var int[]
     */
    public $backoff = [60, 300];

    /**
     * Número do processo solicitado
     *
     * @var string
     */
    private $processo;

    /**
     * Usuário solicitante.
     */
    private Usuario $solicitante;

    /**
     * Destino dos processos solicitados.
     */
    private Lotacao $destino;

    /**
     * Data e hora da solicitação
     */
    private Carbon $solicitada_em;

    /**
     * Usuário que cancelou a solicitação.
     */
    private Usuario $operador;

    /**
     * Data e hora do cancelamento da solicitação.
     */
    private Carbon $cancelada_em;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\stdClass $solicitacao)
    {
        $this->processo = $solicitacao->processo;
        $this->solicitante = $solicitacao->solicitante->withoutRelations();
        $this->destino = $solicitacao->destino->withoutRelations();
        $this->solicitada_em = $solicitacao->solicitada_em;
        $this->operador = $solicitacao->operador->withoutRelations();
        $this->cancelada_em = $solicitacao->cancelada_em;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Notification::send(
            $this->solicitante,
            new SolicitacaoCancelada(
                $this->processo,
                $this->solicitante->nome ?: $this->solicitante->matricula,
                $this->destino->nome ?: $this->destino->sigla,
                $this->solicitada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                $this->operador->nome ?: $this->operador->matricula,
                $this->cancelada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                route('solicitacao.index')
            )
        );
    }

    /**
     * Get o cache driver usado para determinar o lock para jobs únicos.
     *
     * @return \Illuminate\Contracts\Cache\Repository
     */
    public function uniqueVia()
    {
        return cache()->driver('database');
    }
}
