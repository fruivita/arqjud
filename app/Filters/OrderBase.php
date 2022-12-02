<?php

namespace App\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
abstract class OrderBase
{
    /**
     * Aplica por pipe ordenação à query caso haja na query string do request
     * a chave `order` válida.
     *
     * Em qualquer caso, aplica ordenação desc pelo ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Closure $next
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, Closure $next)
    {
        collect(request()->query('order'))
            ->filter()
            ->each(function (string $direcao, string $coluna) use ($query) {
                if (method_exists($this, $coluna)) {
                    $this->{$coluna}($query, $direcao);
                }
            });

        $query->orderBy('id', 'desc');

        return $next($query);
    }
}
