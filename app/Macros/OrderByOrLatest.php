<?php

namespace App\Macros;

use Illuminate\Database\Query\Builder;

/**
 * Aplica cláusula orderBy para cada item do array de colunas/direções
 * informadas.
 *
 * Se nada for informado, ordenará pela data de criação do mais recente para o
 * mais antigo e, subsidiariamente, do maior para o menor id.
 *
 * @method static \Illuminate\Contracts\Database\Query\Builder when($colunas, callable $callback = null, callable $default = null)
 *
 * @param  null|array<string, string>  $colunas [coluna, direção]
 * @return \Illuminate\Database\Query\Builder
 *
 * @see https://dzone.com/articles/how-to-use-laravel-macro-with-example
 * @see https://qirolab.com/posts/what-are-laravel-macros-and-how-to-extending-laravels-core-classes-using-macros
 */
class OrderByOrLatest
{
    public function __invoke()
    {
        return function (?array $colunas) {
            $this->when(
                $colunas,
                function (Builder $query, array $colunas) {
                    foreach ($colunas as $coluna => $direcao) {
                        $query->orderBy($coluna, $direcao);
                    }
                },
                function (Builder $query) {
                    $query->latest()->orderBy('id', 'desc');
                }
            );

            return $this;
        };
    }
}
