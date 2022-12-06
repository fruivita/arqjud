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

if (!function_exists('mascara')) {
    /**
     * Aplica determinada máscara à string informada. Se a máscara não for
     * compatível com a string informada, retorna-a sem o mascaramento.
     *
     * Exemplo: 1234567 com mascara ##.##-### retornará 12.34-567
     * Exemplo: 123 com mascara ##.##-### retornará 123
     *
     * @param  string  $valor
     * @param  string  $mascara
     * @return string
     */
    function mascara(string $valor, string $mascara)
    {
        $mascara = str($mascara);

        if ($mascara->substrCount('#') !== str($valor)->length()) {
            return $valor;
        }

        return sprintf(
            $mascara->replace('#', '%s')->toString(),
            ...str_split($valor)
        );
    }
}

if (!function_exists('apenasNumeros')) {
    /**
     * Retorna apenas a parte numérica da string informada.
     *
     * Exemplo: 123.456-AB retornará 123456
     *
     * @param  string|null  $valor
     * @return string|null
     */
    function apenasNumeros(string $valor = null)
    {
        $numeros = str($valor)->replaceMatches('/[^0-9]++/', '')->toString();

        return $numeros === ''
            ? null
            : $numeros;
    }
}
