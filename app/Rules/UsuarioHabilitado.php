<?php

namespace App\Rules;

use App\Models\Usuario;
use Illuminate\Contracts\Validation\InvokableRule;

/**
 * Verifica se o usuário está habilitado no fluxo de remessa dos processos.
 *
 * Campos mínimos para habilitação:
 * - Nome;
 * - Matrícula;
 * - Sigla de rede;
 * - Email;
 * - Lotação.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 */
class UsuarioHabilitado implements InvokableRule
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
        $usuario = Usuario::query()
            ->where('username', $value)
            ->whereNotNull('matricula')
            ->whereNot('matricula', '')
            ->whereNotNull('email')
            ->whereNot('email', '')
            ->whereNotNull('nome')
            ->whereNot('nome', '')
            ->whereNotNull('lotacao_id')
            ->where('lotacao_id', '>', 0)
            ->first();

        if (empty($usuario)) {
            $fail('validation.habilitado')->translate();
        }
    }
}
