<?php

namespace App\Http\Livewire\Traits;

/**
 * Trait para limitar a quantidade de registros do eager loading.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait ComLimite
{
    /**
     * Limite padrão.
     *
     * @var int
     */
    public $limite = 10;
}
