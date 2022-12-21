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
    private $username;

    /**
     * @param  string|null  $username
     */
    public function __construct(string $username = null)
    {
        $this->username = $username;
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
            'samaccountname' => $this->username,
            'password' => $value,
        ]);

        if ($valido !== true) {
            $fail('auth.password')->translate();
        }
    }
}
