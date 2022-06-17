<?php

namespace App\Macros;

use Illuminate\Database\Query\Builder;

/**
 * Apply orderBy clause to the informed column.
 *
 * If the column is not present, it will sort by creation date from newest to
 * oldest.
 *
 * @param string $column    column name
 * @param string $direction asc|desc
 *
 * @return \Illuminate\Database\Query\Builder
 */
class OrderByWhen
{
    public function __invoke()
    {
        return function (string $column, string $direction) {

            $this->when($column,

                function(Builder $query, string $column) use ($direction) {
                    $query->orderBy($column, $direction);
                },

                function(Builder $query) {
                    $query->latest();
                }
            );

            return $this;
        };
    }
}
