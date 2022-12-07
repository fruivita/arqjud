<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

/**
 * Determina se o número do processo judicial é válido de acordo as regras
 * determinadas pelo Conselho Nacional de Justiça.
 *
 * Notar que o número pode ser informado com qualquer máscara, inclusive sem.
 *
 *  VI – A verificação da correção do número único do processo deve ser
 * realizada pela aplicação da seguinte fórmula, cujo resultado deve ser
 * igual a 1 (um):
 * N6 N5 N4 N3 N2 N1 N0 A3 A2 A1 A0 J2 T1 R0 O3 O2 O1 O0 D1 D0 módulo 97
 *
 * Exemplo de número de processo: 0000100-15.2008.100.0000
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 * @see https://atos.cnj.jus.br/atos/detalhar/119
 * @see https://atos.cnj.jus.br/files/resolucao_65_16122008_04032013165912.pdf
 */
class NumeroProcessoCNJ implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value número do processo sem máscara
     * @param  \Closure  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $numero = apenasNumeros($value);

        if (empty($numero) || $this->modulo($numero) !== 1) {
            $fail('validation.cnj')->translate();
        }
    }

    /**
     * Aplica o algoritmo módulo 97 na base 10 ao número de processo informado.
     *
     * Exemplo de número de processo: 0000100-15.2008.100.0000
     *
     * N6 N5 N4 N3 N2 N1 N0 A3 A2 A1 A0 J2 T1 R0 O3 O2 O1 O0 D1 D0 módulo 97
     *
     * @param  string  $numero
     * @return int resultado do módulo 97 na base 10
     */
    private function modulo(string $numero)
    {
        $partes = $this->partes($numero);

        $r1 = $partes['sequencial'] % 97;
        $r2 = "{$r1}{$partes['ano']}{$partes['orgao']}{$partes['tribunal']}" % 97;

        return "{$r2}{$partes['unidade_origem']}{$partes['digito_verificador']}" % 97;
    }

    /**
     * Quebra o número do processo no formato NNNNNNN-DD.AAAA.JTR.OOOO nas
     * partes definidas pelo CNJ.
     *
     * @param  string  $numero
     * @return array<string, string>
     */
    private function partes(string $numero)
    {
        $sem_mascara = str($numero);

        return [
            'sequencial' => $sem_mascara->substr(0, 7)->toString(),
            'digito_verificador' => $sem_mascara->substr(7, 2)->toString(),
            'ano' => $sem_mascara->substr(9, 4)->toString(),
            'orgao' => $sem_mascara->substr(13, 1)->toString(),
            'tribunal' => $sem_mascara->substr(14, 2)->toString(),
            'unidade_origem' => $sem_mascara->substr(16, 4)->toString(),
        ];
    }
}
