<?php

namespace App\Jobs;

use App\Models\Guia;
use App\Models\Usuario;
use App\Notifications\ProcessoEntregue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

/**
 * Notifica os envolvidos na entrega de processos solicitados.
 *
 * @see https://laravel.com/docs/9.x/queues
 */
class NotificarEntrega implements ShouldQueue, ShouldBeUnique
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
     * Usuário recebedor.
     */
    private Usuario $recebedor;

    /**
     * Guia de remessas de processo.
     */
    private Guia $guia;

    /**
     * A entrega foi efetivada por meio de guia de remessa impressa?
     *
     * @var bool
     */
    private $por_guia;

    /**
     * Endereços de email de terceiros que devem ser notificados, isto é, todos
     * os solicitantes dos processos.
     *
     * São considerados terceiros, pois não são, obrigatoriamente, as pessoas
     * que foram buscar os processos.
     *
     * @var string[]
     */
    private $email_terceiros;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\stdClass $entrega)
    {
        $this->recebedor = $entrega->recebedor;
        $this->guia = $entrega->guia;
        $this->por_guia = boolval($entrega->por_guia);
        $this->email_terceiros = $entrega->guia->processos
            ->pluck('solicitante.email')
            ->unique()
            ->toArray();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Notification::send(
            $this->recebedor,
            new ProcessoEntregue(
                $this->guia->paraHumano,
                $this->guia->processos->toArray(),
                data_get($this->guia->recebedor, 'nome') ?: data_get($this->guia->recebedor, 'matricula'),
                $this->guia->destino['nome'] ?: $this->guia->destino['sigla'],
                $this->guia->gerada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                $this->por_guia,
                route('solicitacao.index'),
                $this->email_terceiros
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
