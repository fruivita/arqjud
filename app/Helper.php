<?php

if (!function_exists('ascOrDesc')) {
    /**
     * Determina o valor que deve ser utilizado na ordenção das queries de
     * acordo com a direção informada.
     *
     * Desc é a ordenação padrão, pois, por padrão, os registros são exibidos
     * do mais recente para o mais antigo.
     *
     * @param  string|null  $direcao
     * @return string `asc` ou `desc` (default)
     */
    function ascOrDesc(string $direcao = null)
    {
        return str($direcao)->lower()->exactly('asc') ? 'asc' : 'desc';
    }
}
