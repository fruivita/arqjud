<?php

namespace App\Casts;

use App\Models\Processo;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @see https://laravel.com/docs/9.x/eloquent-mutators
 */
class NumeroProcesso implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * Aplica máscara o número do processo.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     * @return string|null
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return Processo::aplicarMascaraProcesso($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * Remove eventual máscara do número do processo.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     * @return string|null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return apenasNumeros($value);
    }
}
