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
 * @see https://laravel.com/docs/queues
 */
class ImportarEstruturaCorporativa implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
     * Número de segundos até o job deixar de ser considerado único.
     *
     * @var int
     */
    public $uniqueFor = 12 * 60 * 60; // 12 hours

    /**
     * Cria uma nova instância do jbo.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Executa o job.
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
