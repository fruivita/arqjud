<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Stringable;

/**
 * Determina se o número do processo judicial é válido de acordo as regras
 * 1) Do Conselho Nacional de Justiça
 * 2) Da versão 1 com 10 dígitos
 * 3) Da versão 2 com 15 dígitos
 *
 * Notar que o número pode ser informado com qualquer máscara, inclusive sem.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 */
class NumeroProcesso implements InvokableRule
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
        $numero = str(apenasNumeros($value));
        $valido = false;

        switch ($numero->length()) {
            case 20:
                $validator = Validator::make([$attribute => $value], [
                    $attribute => [new NumeroProcessoCNJ()],
                ]);

                $valido = $validator->passes();
                break;

            case 15:
                $valido = $this->validacaoV2($numero);
                break;

            case 10:
                $valido = $this->validacaoV1($numero);
                break;
        }

        if ($valido !== true) {
            $fail('validation.invalid')->translate();
        }
    }

    /**
     * Determina se o número do processo judicial é válido de acordo as regras
     * do formato de 15 dígitos.
     *
     * Exemplo de número de processo: AAAA.SS.LL.NNNNNN-D
     *
     * @param  \Illuminate\Support\Stringable  $numero
     * @return bool
     */
    private function validacaoV2(Stringable $numero)
    {
        $digitos = str_split($numero->toString());
        $digito_verificador = intval(array_pop($digitos));
        $pesos = [7, 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $somatorio = 0;

        foreach ($digitos as $indice => $digito) {
            $somatorio += intval($digito) * $pesos[$indice];
        }

        $dv = $somatorio % 11;

        $dv = ($dv == 10) ? 0 : intval($dv);

        return $digito_verificador === $dv;
    }

    /**
     * Determina se o número do processo judicial é válido de acordo as regras
     * do formato de 10 dígitos.
     *
     * Exemplo de número de processo: AANNNNNNND
     *
     * @param  \Illuminate\Support\Stringable  $numero
     * @return bool
     */
    private function validacaoV1(Stringable $numero)
    {
        $digitos = str_split($numero->toString());
        $digito_verificador = intval(array_pop($digitos));
        $pesos = [1, 2, 3, 4, 5, 6, 7, 8, 9];

        $somatorio = 0;

        foreach ($digitos as $indice => $digito) {
            $somatorio += intval($digito) * $pesos[$indice];
        }

        $dv = $somatorio % 11;

        $dv = ($dv == 10) ? 0 : intval($dv);

        return $digito_verificador === $dv;
    }
}
