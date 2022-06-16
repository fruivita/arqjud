<?php

namespace App\Http\Livewire\Traits;

/**
 * Trait designed group sortable behaviour.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait WithSorting
{
    /**
     * Column set for sorting.
     *
     * @var string
     */
    public $sort_column;

    /**
     * Direction set for sorting
     *
     * @var string
     */
    public $sort_direction;

    /**
     * Set the ordering direction of the informed column.
     *
     * @param string $column
     *
     * @return void
     */
    public function sortBy(string $column)
    {
        if ($this->sort_column === $column) {

            $this->sort_direction = $this->sort_direction === 'asc' ? 'desc' : 'asc';

        } else {

            $this->sort_direction = 'asc';

        }

        $this->sort_column = $column;
    }
}
