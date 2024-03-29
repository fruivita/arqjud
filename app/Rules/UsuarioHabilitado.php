<?php

namespace App\Rules;

use App\Models\Usuario;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Database\Eloquent\Builder;

/**
 * Verifica se o usuário está habilitado no fluxo de remessa dos processos.
 *
 * Campos mínimos para habilitação:
 * - Nome;
 * - Matrícula;
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
     * @param  mixed  $value matrícula do usuário
     * @param  \Closure  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        /** @var \App\Models\Usuario|null */
        $usuario = Usuario::query()
            ->when(
                is_numeric($value),
                fn (Builder $builder) => $builder->where('id', $value),
                fn (Builder $builder) => $builder->where('matricula', $value)
            )->first();

        if (empty($usuario) || $usuario->habilitado() !== true) {
            $fail('validation.habilitado')->translate();
        }
    }
}
