<?php

namespace App\Http\Livewire\Traits;

/**
 * Trait para agrupar a lógica da ordenação.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait ComOrdenacao
{
    /**
     * Array associativo com colunas e direções para ordenação.
     *
     * @var array
     */
    public array $ordenacoes = [];

    /**
     * Define a direção de ordenação para a coluna informada.
     *
     * @param string $coluna
     *
     * @return mixed
     */
    public function ordenarPor(string $coluna)
    {
        if (isset($this->ordenacoes[$coluna]) === false) {
            return $this->ordenacoes[$coluna] = 'asc';
        }

        if ($this->ordenacoes[$coluna] === 'asc') {
            return $this->ordenacoes[$coluna] = 'desc';
        }

        unset($this->ordenacoes[$coluna]);
    }
}
