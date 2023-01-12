<?php

namespace App\Rules;

use App\Models\Processo;
use Illuminate\Contracts\Validation\InvokableRule;

/**
 * Verifica se o processo informado está disponível para solicitação, ou seja,
 * se não possui nenhuma solicitação ativa (solicitado ou entregue).
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 */
class ProcessoDisponivel implements InvokableRule
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
        $processo = Processo::doesntHave('solicitacoesAtivas')
            ->firstWhere('numero', $value);

        if (!$processo) {
            $fail('validation.solicitacao.indisponivel')->translate();
        }
    }
}
