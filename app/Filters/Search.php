<?php

namespace App\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class Search
{
    /**
     * Aplica por pipe o escopo search do modelo caso haja na query string do
     * request a chave `termo` válida.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Closure  $next
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, Closure $next)
    {
        $termo = request()->string('termo')->trim();

        if ($termo->length() >= 1) {
            $query->search($termo->toString());
        }

        return $next($query);
    }
}
