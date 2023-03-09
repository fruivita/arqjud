<?php

namespace App\Jobs;

use App\Models\Lotacao;
use App\Models\Usuario;
use App\Notifications\ProcessoSolicitado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

/**
 * Notifica o solicitante acerca de solicitação de processos feita em seu nome.
 *
 * @see https://laravel.com/docs/9.x/queues
 */
class NotificarSolicitanteSolicitacao implements ShouldQueue, ShouldBeUnique
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
     * Número dos processos solicitados
     *
     * @var string[]
     */
    private $processos;

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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\stdClass $solicitacao)
    {
        $this->processos = $solicitacao->processos;
        $this->solicitante = $solicitacao->solicitante->withoutRelations();
        $this->destino = $solicitacao->destino->withoutRelations();
        $this->solicitada_em = $solicitacao->solicitada_em;
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
            new ProcessoSolicitado(
                $this->processos,
                $this->solicitante->nome ?: $this->solicitante->matricula,
                $this->destino->nome ?: $this->destino->sigla,
                $this->solicitada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
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
