<?php

namespace App\Console\Commands;

use App\Enums\Queue;
use App\Jobs\ImportarDadosRH as ImportarDadosRHJob;
use Illuminate\Console\Command;

/**
 * @link https://laravel.com/docs/9.x/artisan
 */
class ImportarDadosRH extends Command
{
    /**
     * O nome e a assinatura do comando de console.
     *
     * @var string
     */
    protected $signature = 'rh:importar';

    /**
     * Descrição do comando de console.
     *
     * @var string
     */
    protected $description = 'Importa a estrutura corporativa (RH) do orgao';

    /**
     * Executa o comando de console.
     *
     * @return int
     */
    public function handle()
    {
        ImportarDadosRHJob::dispatch()->onQueue(Queue::Alta->value);

        return 0;
    }
}
