<?php

namespace App\Jobs;

use FruiVita\Corporativo\Facades\Corporativo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Importa os dados de recursos humanos.
 *
 * - Pessoas/Usuários;
 * - Cargos;
 * - Funções de confiança;
 * - Lotação.
 *
 * Útil para manter completo os dados dos usuários e habilitá-los nas
 * funcionalidades relativas à interação com a cessão de processos arquivados.
 *
 * @see https://laravel.com/docs/9.x/queues
 */
class ImportarDadosRH implements ShouldQueue, ShouldBeUnique
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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Corporativo::importar(
            config('orgao.arquivo_corporativo')
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
