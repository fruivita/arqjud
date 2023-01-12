<?php

namespace App\Rules;

use App\Models\Processo;
use Illuminate\Contracts\Validation\InvokableRule;

/**
 * Verifica se o processo informado pode ser retornado ao arquivo.
 *
 * Basicamente, verifica se há solicitação no status entregue, visto que as
 * solicitadas e as devolvidas já ou ainda estão sob a guarda do arquivo.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 */
class ProcessoRetornavel implements InvokableRule
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
        $processo = Processo::has('solicitacoesEntregues')
            ->firstWhere('numero', $value);

        if (!$processo) {
            $fail('validation.solicitacao.retornavel')->translate();
        }
    }
}
