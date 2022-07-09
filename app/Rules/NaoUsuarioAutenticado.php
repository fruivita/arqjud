<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Usuário informado não é o usuário autenticado.
 *
 * @see https://laravel.com/docs/validation#custom-validation-rules
 */
class NaoUsuarioAutenticado implements Rule
{
    /**
     * Determina se a regra de validação passou.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return (auth()->user()
            && auth()->user()->username != $value)
                ? true
                : false;
    }

    /**
     * Mensagem de erro de validação.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.not_current_user');
    }
}
