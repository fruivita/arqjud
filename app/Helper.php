<?php

if (! function_exists('maxIntegerSeguro')) {
    /**
     * Integer máximo aceito pelo JavaScript. Útil para aplicações Livewire.
     *
     * @return int
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/MAX_SAFE_INTEGER
     * @see https://github.com/livewire/livewire/discussions/4788
     */
    function maxIntegerSeguro()
    {
        return pow(2, 53) - 1;
    }
}

if (! function_exists('stringParaArrayAssoc')) {
    /**
     * Divida uma string com base no delimitador e a retorna como um array
     * associativo.
     *
     * Os valores extraídos devem ser numericamente compatívis com o número de
     * chaves informada, caso contrário irá retornar null.
     * Irá retornar null também se algum parametro for falso para o php.
     *
     * @param string[] $chaves      chaves que serão utilizadas como índice do
     *                              array
     * @param string   $string      string que será quebrada
     * @param string   $delimitador delimitador para a quebra da string
     *
     * @return array<string, string>|null
     *
     * @see https://www.php.net/manual/en/language.types.boolean.php
     */
    function stringParaArrayAssoc(array $chaves, string $delimitador, string $string)
    {
        if (! $chaves || ! $delimitador || ! $string) {
            return null;
        }

        try {
            return
                array_combine(
                    $chaves,
                    explode($delimitador, $string)
                );
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
