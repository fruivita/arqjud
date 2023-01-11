<?php

namespace App\Jobs;

use App\Services\Importador\ImportadorArquivoProcesso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * @see https://laravel.com/docs/9.x/queues
 */
class ImportarProcesso implements ShouldQueue, ShouldBeUnique
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
     * Nome do arquivo de processo que será importado.
     *
     * Ex.: foo.csv
     *
     * @var string
     */
    private $arquivo;

    /**
     * Create a new job instance.
     *
     * @param $arquivo nome do arquivo de processo que será importado
     * @return void
     */
    public function __construct(string $arquivo)
    {
        $this->arquivo = $arquivo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ImportadorArquivoProcesso::make()->importar($this->arquivo);
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
