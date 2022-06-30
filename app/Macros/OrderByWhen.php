<?php

namespace App\Macros;

use Illuminate\Database\Query\Builder;

/**
 * Apply orderBy clause to the informed set of column and direction.
 *
 * If the column is not present, it will sort by creation date from newest to
 * oldest.
 *
 * @param array<string, string> $sorts [column, direction]
 *
 * @return \Illuminate\Database\Query\Builder
 */
class OrderByWhen
{
    public function __invoke()
    {
        return function ($sorts) {

            $this->when($sorts,

                function(Builder $query, array $sorts) {
                    foreach ($sorts as $column => $direction) {
                        $query->orderBy($column, $direction);
                    }
                },

                function(Builder $query) {
                    $query->latest()->orderBy('id', 'desc');
                }
            );

            return $this;
        };
    }
}
