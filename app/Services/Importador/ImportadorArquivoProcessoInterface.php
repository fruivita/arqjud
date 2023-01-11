<?php

namespace App\Services\Importador;

/**
 * @see https://m.dotdev.co/design-pattern-service-layer-with-laravel-5-740ff0a7b65f
 * @see https://blackdeerdev.com/laravel-services-pattern/
 */
interface ImportadorArquivoProcessoInterface
{
    /**
     * Importa os processos existentes no arquivo informado.
     *
     * O arquivo deve ser do tipo CSV e estar no Storage processo.
     *
     *
     * @param  string  $arquivo nome do arquivo apenas, não é o full path
     * @return void
     */
    public function importar(string $arquivo);
}
