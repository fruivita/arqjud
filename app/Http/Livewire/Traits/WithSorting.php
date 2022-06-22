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
     * Associative array with columns and directions for sorting
     *
     * @var array
     */
    public array $sorts = [];

    /**
     * Set the ordering direction of the informed column.
     *
     * @param string $column
     *
     * @return mixed
     */
    public function sortBy(string $column)
    {
        if (isset($this->sorts[$column])  === false) {
            return $this->sorts[$column] = 'asc';
        }

        if ($this->sorts[$column]  === 'asc') {
            return $this->sorts[$column] = 'desc';
        }

        unset($this->sorts[$column]);
    }
}
