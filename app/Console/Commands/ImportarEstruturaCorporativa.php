<?php

namespace App\Console\Commands;

use App\Enums\Importacao;
use App\Jobs\ImportarEstruturaCorporativa as ImportarEstruturaCorporativaJob;
use Illuminate\Console\Command;

/**
 * @see https://laravel.com/docs/artisan
 */
class ImportarEstruturaCorporativa extends Command
{
    /**
     * O nome e a assinatura do comando de console.
     *
     * @var string
     */
    protected $signature = 'importar:corporativo';

    /**
     * Descrição do comando de console.
     *
     * @var string
     */
    protected $description = 'Importa a estrutura corporativa do orgao.';

    /**
     * Executa o comando de console.
     *
     * @return int
     */
    public function handle()
    {
        ImportarEstruturaCorporativaJob::dispatch()
        ->onQueue(Importacao::Corporativo->queue());

        return 0;
    }
}
