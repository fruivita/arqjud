<?php

namespace App\Http\Livewire\Traits;

use Livewire\WithPagination;

/**
 * Trait to define the pagination used.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithPerPagePagination
{
    use WithPagination;

    /**
     * Sets the default view for pagination.
     *
     * @return string
     */
    public function paginationView()
    {
        return 'components.pagination';
    }

    /**
     * Volta a exibição para a primeira página.
     *
     * Runs after a property called `$preferencias['por_pagina' => $valor]` is
     * updated
     *
     * @return void
     */
    public function updatedPreferenciasPorPagina()
    {
        $this->resetPage();
    }
}
