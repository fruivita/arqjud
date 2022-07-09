<?php

namespace App\Models\Traits;

/**
 * Transforma valores em formato de fácil leitura humana.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 */
trait ComHumanizacao
{
    /**
     * Estante em formato para leitura humana.
     *
     * @param int $numero número da estante
     *
     * @return mixed
     */
    private function humanizarEstante(int $numero)
    {
        return $numero ?: __('Não informada');
    }

    /**
     * Prateleira em formato para leitura humana.
     *
     * @param int $numero número da prateleira
     *
     * @return string|int
     */
    private function humanizarPrateleira(int $numero)
    {
        return $numero ?: __('Não informada');
    }

    /**
     * Caixa em formato para leitura humana.
     *
     * @param int $numero número da caixa
     * @param int $ano    ano da caixa
     *
     * @return string
     */
    private function humanizarCaixa(int $numero, int $ano)
    {
        return "{$numero}/{$ano}";
    }

    /**
     * Volume da caixa em formato para leitura humana.
     *
     * @param int $numero número do volume da caixa
     *
     * @return string
     */
    private function humanizarVolumeCaixa(int $numero)
    {
        return "Vol. {$numero}";
    }
}
