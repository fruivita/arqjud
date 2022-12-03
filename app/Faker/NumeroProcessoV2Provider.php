<?php

namespace App\Faker;

use Faker\Provider\Base;

/**
 * Gerador de números de processos judiciais brasileiros no padrão 15 digitos.
 *
 * Os processos novos não seguem mais essa regra, contudo, processos antigos a
 * utilizam.
 */
class NumeroProcessoV2Provider extends Base
{
    /**
     * Gera números de processos judiciais brasileiros no padrão 15 dígitos sem
     * aplicação de máscara, isto é, apenas a string numérica.
     *
     * Os números gerados são fictícios. Se verídicos, terá sido uma mera
     * coincidência.
     *
     * Padrão: AAAASSLLNNNNNND onde
     * - AAAA é o ano com 4 dígitos;
     * - SS é a Seção com 2 dígitos;
     * - LL é a Localidade de Origem com 2 dígitos;
     * - NNNNNN é o Número do Processo com 6 dígitos;
     * - D é o dígito verificador com 1 dígito.
     *
     * Exemplo: 201250010016907
     *
     * @return string
     */
    public static function numeroProcessoV2()
    {
        $ano = self::ano();
        $secao = self::secao();
        $localidade = self::localidade();
        $sequencial = self::sequencial();
        $digito_verificador = self::gerarDigitoVerificador(
            $ano,
            $secao,
            $localidade,
            $sequencial
        );

        return "{$ano}{$secao}{$localidade}{$sequencial}{$digito_verificador}";
    }

    /**
     * Ano de ajuizamento da ação com dois dígitos e tantos zeros à esquerda
     * quanto forem necessários.
     *
     * Exemplo: 09
     *
     * @return int
     */
    private static function ano()
    {
        return static::numberBetween(1900, intval(date('Y')));
    }

    /**
     * Seção do processo com 2 dígitos.
     *
     * Exemplo: 05
     *
     * @return string
     */
    private static function secao()
    {
        return static::numerify('##');
    }

    /**
     * Localidade de origem do processo 2 dígitos.
     *
     * Exemplo: 05
     *
     * @return string
     */
    private static function localidade()
    {
        return static::numerify('##');
    }

    /**
     * Retorna um número de 6 dígitos com tantos zeros à esquerda quanto forem
     * necessários.
     *
     * Exemplo: 000010
     *
     * @return string
     */
    private static function sequencial()
    {
        return static::numerify('######');
    }

    /**
     * Calcula o dígito verificador do número do processo composto de um único
     * dígito.
     *
     * Cálculo:
     * - 1ª Fase - multiplicar cada digito do ano, seção, localidade e número
     * por seu respectivo peso e somar o resultado obtido.
     * -------------------------------------------
     * A1: 1º digito do ano multiplicado por 7
     * A2: 2º digito do ano multiplicado por 6
     * A3: 3º digito do ano multiplicado por 5
     * A4: 4º digito do ano multiplicado por 4
     * S1: 1º digito da seção multiplicado por 3
     * S2: 2º digito da seção multiplicado por 2
     * L1: 1º digito da localidade multiplicado por 9
     * L2: 2º digito da localidade multiplicado por 8
     * D1: 1º digito do número multiplicado por 7
     * D2: 2º digito do número multiplicado por 6
     * D3: 3º digito do número multiplicado por 5
     * D4: 4º digito do número multiplicado por 4
     * D5: 5º digito do número multiplicado por 3
     * D6: 6º digito do número multiplicado por 2
     * -------------------------------------------
     *
     * - 2ª Fase - aplicar a operação Módulo 11 no valor obtido na 1ª fase.
     * O resultado da operação é o valor do dígito. Contudo, se o valor 10,
     * deve-se retornar 0.
     *
     * Exemplo: 0 ou 9
     *
     * @param  int  $ano
     * @param  string  $secao
     * @param  string  $localidade
     * @param  string  $sequencial
     * @return int
     */
    private static function gerarDigitoVerificador(int $ano, string $secao, string $localidade, string $sequencial)
    {
        $digitos = str_split("{$ano}{$secao}{$localidade}{$sequencial}");
        $pesos = [7, 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $somatorio = 0;

        foreach ($digitos as $indice => $digito) {
            $somatorio += intval($digito) * $pesos[$indice];
        }

        $digito_verificador = $somatorio % 11;

        return ($digito_verificador == 10)
            ? 0
            : $digito_verificador;
    }
}
