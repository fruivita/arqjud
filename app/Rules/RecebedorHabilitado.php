<?php

namespace App\Rules;

use App\Models\Usuario;
use Illuminate\Contracts\Validation\InvokableRule;

/**
 * Verifica se o usuário está habilitado a receber as solicitações de processo,
 * isto é, se ele está lotado em alguma lotação.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 */
class RecebedorHabilitado implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value username do usuário
     * @param  \Closure  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $usuario = Usuario::firstWhere('username', $value);

        if (empty($usuario) || empty($usuario->lotacao_id) || $usuario->lotacao_id <= 0) {
            $fail('validation.autorizacao')->translate();
        }
    }
}
