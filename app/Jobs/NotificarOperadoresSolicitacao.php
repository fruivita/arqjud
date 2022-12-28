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
 * Notifica os usuários de perfil operador acerca de solicitação de processo
 * feita pelo usuário.
 *
 * @see https://laravel.com/docs/9.x/queues
 */
class NotificarOperadoresSolicitacao implements ShouldQueue, ShouldBeUnique
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
     *
     * @var \App\Models\Usuario
     */
    private Usuario $solicitante;

    /**
     * Destino dos processos solicitados.
     *
     * @var \App\Models\Lotacao
     */
    private Lotacao $destino;

    /**
     * Data e hora da solicitação
     *
     * @var \Illuminate\Support\Carbon
     */
    private Carbon $solicitada_em;

    /**
     * Create a new job instance.
     *
     * @param  \stdClass  $solicitacao
     * @return void
     */
    public function __construct(\stdClass $solicitacao)
    {
        $this->processos = $solicitacao->processos;
        $this->solicitante = $solicitacao->solicitante->withoutRelations();
        $this->destino = $solicitacao->destino->withoutRelations();
        $this->solicitada_em = now();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->solicitante->loadMissing('lotacao');

        Notification::send(
            Usuario::operadores()->get(),
            new ProcessoSolicitado(
                $this->processos,
                $this->solicitante->nome ?: $this->solicitante->username,
                $this->destino->nome ?: $this->destino->sigla,
                $this->solicitada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                route('atendimento.solicitar-processo.index')
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
