<?php

namespace App\Console\Commands;

use App\Enums\Queue;
use App\Jobs\ImportarProcesso as ImportarProcessoJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * @see https://laravel.com/docs/9.x/artisan
 */
class ImportarProcesso extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'processo:importar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa os processos do arquivo de carga do tipo CSV';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $arquivo = $this->ask('Nome do arquivo para importação? (Apenas arquivos CSV são aceitos)');

        if (Storage::disk('processo')->missing($arquivo)) {
            $this->error(__('Arquivo não encontrado'));

            return 1;
        }

        if (Storage::disk('processo')->mimeType($arquivo) !== 'text/csv') {
            $this->error(__('O arquivo foi encontrado, porém não é um arquivo validamente CSV'));

            return 2;
        }

        ImportarProcessoJob::dispatch($arquivo)->onQueue(Queue::Baixa->value);

        $this->info(__('Arquivo posto na fila de processamento'));

        return 0;
    }
}
