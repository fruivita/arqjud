<?php

namespace App\Macros;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;

/**
 * Aplica cláusula orWhere para o array de colunas informado utilizando o
 * operador 'like' se o `$termo` para pesquisa for informado.
 *
 * Notar que a cláusula é or é aplicada apenas entre os elementos presentes nas
 * colunas.
 *
 * @method static \Illuminate\Contracts\Database\Query\Builder when($valor, callable $callback = null, callable $default = null)
 *
 * @param string|string[] $colunas
 * @param string          $termo
 *
 * @return \Illuminate\Database\Query\Builder
 *
 * @see https://dzone.com/articles/how-to-use-laravel-macro-with-example
 * @see https://qirolab.com/posts/what-are-laravel-macros-and-how-to-extending-laravels-core-classes-using-macros
 */
class OrWhereLike
{
    public function __invoke()
    {
        return function ($colunas, $termo) {
            $this->when(
                str()->length($termo) >= 1,
                function (Builder $query) use ($colunas, $termo) {
                    $query->where(function (Builder $query) use ($colunas, $termo) {
                        foreach (Arr::wrap($colunas) as $coluna) {
                            $query->orWhere($coluna, 'like', "%{$termo}%");
                        }
                    });
                }
            );

            return $this;
        };
    }
}
