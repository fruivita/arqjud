<?php

namespace App\Pipes\Importacao;

use App\Enums\Queue;
use App\Jobs\ImportarDadosRH;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class Importar
{
    /**
     * Executa por pipe a importação dos dados solicitados caso as importações
     * solicitadas sejam permitidas.
     *
     * @return \stdClass
     */
    public function handle(\stdClass $importacao, \Closure $next)
    {
        collect($importacao->importacoes)
            ->filter()
            ->each(function (string $importar) {
                $importar = str()->camel($importar);

                if (method_exists($this, $importar)) {
                    $this->{$importar}();
                }
            });

        return $next($importacao);
    }

    /**
     * Dispara o job para a importação do arquivo corporativo, isto é, dos
     * dados de recursos humanos.
     *
     * @return void
     */
    protected function rh()
    {
        ImportarDadosRH::dispatch()->onQueue(Queue::Alta->value);
    }
}
