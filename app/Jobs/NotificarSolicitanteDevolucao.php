<?php

namespace App\Jobs;

use App\Models\Usuario;
use App\Notifications\ProcessoDevolvido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

/**
 * Notifica o solicitante sobre a devolução ao arquivo de processo por ele
 * solicitado.
 *
 * @see https://laravel.com/docs/9.x/queues
 */
class NotificarSolicitanteDevolucao implements ShouldQueue, ShouldBeUnique
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
     * Número do processo devolvido
     *
     * @var string
     */
    private $processo;

    /**
     * Data e hora da devolução
     */
    private Carbon $devolvido_em;

    /**
     * Usuário solicitante.
     */
    private Usuario $solicitante;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\stdClass $devolucao)
    {
        $this->processo = $devolucao->processo;
        $this->devolvido_em = $devolucao->devolvido_em;
        $this->solicitante = $devolucao->solicitante->withoutRelations();
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
            new ProcessoDevolvido(
                $this->processo,
                $this->devolvido_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
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
