<?php

namespace App\Rules;

use App\Models\Processo;
use Illuminate\Contracts\Validation\InvokableRule;

/**
 * Verifica se o processo informado está disponível para ser movimentado, isto
 * é, se ele está sob a guarda do arquivo.
 *
 * Processos com solicitação entregue não podem ser movimentados, pois estão
 * sob a guarda de terceiros.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 */
class ProcessoMovimentavel implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value número do processo com ou sem a máscara
     * @param  \Closure  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $processo = Processo::doesntHave('solicitacoesEntregues')
            ->firstWhere('numero', $value);

        if (!$processo) {
            $fail('validation.movimentacao')->translate();
        }
    }
}
