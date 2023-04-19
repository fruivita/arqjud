<?php

namespace App\Rules;

use App\Models\Processo;
use Illuminate\Contracts\Validation\InvokableRule;

/**
 * Verifica se para o processo em questão, o solicitante pode ser notificado.
 *
 * Basicamente, verifica se a solicitação do processo está no status solicitada.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 */
class ProcessoNotificavel implements InvokableRule
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
        $processo = Processo::has('solicitacoesSolicitadas')
            ->firstWhere('numero', $value);

        if (!$processo) {
            $fail('validation.solicitacao.notificavel')->translate();
        }
    }
}
