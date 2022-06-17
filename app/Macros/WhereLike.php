<?php

namespace App\Macros;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;

/**
 * Apply orWhere clauses to the various columns informed using the 'like'
 * operator if $value is present.
 *
 * @param string[]|string $columns
 * @param string          $value
 *
 * @return \Illuminate\Database\Query\Builder
 */
class WhereLike
{
    public function __invoke()
    {
        return function ($columns, $value) {

            $this->when($value, function(Builder $query, string $value) use ($columns) {

                $query->where(function (Builder $query) use ($columns, $value) {
                    foreach (Arr::wrap($columns) as $column) {
                        $query->orWhere($column, 'like', "%{$value}%");
                    }
                });

            });

            return $this;
        };
    }
}
