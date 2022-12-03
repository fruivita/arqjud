<?php

namespace App\Faker;

use Faker\Provider\Base;

/**
 * Gerador de números de processos judiciais brasileiros no padrão 10 digitos.
 *
 * Os processos novos não seguem mais essa regra, contudo, processos antigos a
 * utilizam.
 */
class NumeroProcessoV1Provider extends Base
{
    /**
     * Gera números de processos judiciais brasileiros no padrão 10 dígitos sem
     * aplicação de máscara, isto é, apenas a string numérica.
     *
     * Os números gerados são fictícios. Se verídicos, terá sido uma mera
     * coincidência.
     *
     * Padrão: AANNNNNNND onde
     * - AA é o ano com 2 dígitos;
     * - NNNNNNN é um número com 7 dígitos;
     * - D é o dígito verificador com 1 dígito.
     *
     * Exemplo: 9912345678
     *
     * @return string
     */
    public static function numeroProcessoV1()
    {
        $ano = self::ano();
        $sequencial = self::sequencial();
        $digito_verificador = self::gerarDigitoVerificador(
            $ano,
            $sequencial
        );

        return "{$ano}{$sequencial}{$digito_verificador}";
    }

    /**
     * Ano de ajuizamento da ação com dois dígitos e tantos zeros à esquerda
     * quanto forem necessários.
     *
     * Exemplo: 09
     *
     * @return string
     */
    private static function ano()
    {
        return static::numerify('##');
    }

    /**
     * Retorna um número de 7 dígitos com tantos zeros à esquerda quanto forem
     * necessários.
     *
     * Exemplo: 0000100
     *
     * @return string
     */
    private static function sequencial()
    {
        return static::numerify('#######');
    }

    /**
     * Calcula o dígito verificador do número do processo composto de um único
     * dígito.
     *
     * Cálculo:
     * - 1ª Fase - multiplicar cada digito do ano e número por seu respectivo
     * peso e somar o resultado obtido.
     * -------------------------------------------
     * A1: 1º digito do ano multiplicado por 1
     * A2: 2º digito do ano multiplicado por 2
     * D1: 1º digito do número multiplicado por 3
     * D2: 2º digito do número multiplicado por 4
     * D3: 3º digito do número multiplicado por 5
     * D4: 4º digito do número multiplicado por 6
     * D5: 5º digito do número multiplicado por 7
     * D6: 6º digito do número multiplicado por 8
     * D7: 7º digito do número multiplicado por 9
     * -------------------------------------------
     *
     * - 2ª Fase - aplicar a operação Módulo 11 no valor obtido na 1ª fase.
     * O resultado da operação é o valor do dígito. Contudo, se o valor 10,
     * deve-se retornar 0.
     *
     * Exemplo: 0 ou 9
     *
     * @param  string  $ano
     * @param  string  $sequencial
     * @return int
     */
    private static function gerarDigitoVerificador(string $ano, string $sequencial)
    {
        $digitos = str_split("{$ano}{$sequencial}");
        $pesos = [1, 2, 3, 4, 5, 6, 7, 8, 9];

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
