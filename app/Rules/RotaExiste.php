<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Route;

/**
 * Determina se a rota informada existe na aplicação, isto é, se é uma rota
 * válida.
 *
 * @see https://laravel.com/docs/validation#custom-validation-rules
 */
class RotaExiste implements Rule
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
        return Route::has($value);
    }

    /**
     * Mensagem de erro de validação.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.not_found.route');
    }
}
