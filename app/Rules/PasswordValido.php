<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Facades\Auth;

/**
 * Valida a senha do usuário informado.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 */
class PasswordValido implements InvokableRule
{
    /**
     * @var string
     */
    private $matricula;

    /**
     * @param  string|null  $matricula
     */
    public function __construct(string $matricula = null)
    {
        $this->matricula = $matricula;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value senha de rede do usuário
     * @param  \Closure  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $valido = Auth::validate([
            'matricula' => $this->matricula,
            'password' => $value,
        ]);

        if ($valido !== true) {
            $fail('auth.failed')->translate();
        }
    }
}
