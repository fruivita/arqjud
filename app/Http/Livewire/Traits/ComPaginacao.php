<?php

namespace App\Http\Livewire\Traits;

use Livewire\WithPagination;

/**
 * Trait para operações comuns de paginação.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait ComPaginacao
{
    use WithPagination;

    /**
     * Define a view padrão para paginação.
     *
     * @return string
     */
    public function paginationView()
    {
        return 'components.pagination';
    }

    /**
     * Retorna a paginação à sua página inicial.
     *
     * Executado após a propriedade `$preferencias['por_pagina' => $valor]` ser
     * atualizada
     *
     * @return void
     */
    public function updatedPreferenciasPorPagina()
    {
        $this->resetPage();
    }
}
