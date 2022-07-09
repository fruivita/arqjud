<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;

/**
 * Verifica se o arquivo existe no storage informado.
 *
 * @see https://laravel.com/docs/validation#custom-validation-rules
 */
class ArquivoExiste implements Rule
{
    /**
     * Nome do storage onde a existência do arquivo será verificada.
     *
     * @var string
     */
    public $nome_storage;

    /**
     * @param string $nome_storage
     *
     * @return void
     */
    public function __construct(string $nome_storage)
    {
        $this->nome_storage = $nome_storage;
    }

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
        return Storage::disk($this->nome_storage)->exists($value);
    }

    /**
     * Mensagem de erro de validação.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.not_found.file');
    }
}
