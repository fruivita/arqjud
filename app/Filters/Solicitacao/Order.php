<?php

namespace App\Filters\Solicitacao;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Pressupõe join com as tabelas pais se o critério de ordenação for por elas.
 *
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class Order
{
    /**
     * Aplica por pipe ordenação à query caso haja na query string do request
     * a chave `order` válida.
     *
     * Em qualquer caso, aplica ordenação desc pelo ID.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Closure  $next
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $query, Closure $next)
    {
        collect(request()->query('order'))
            ->filter(fn (string $direcao, string $coluna) => (method_exists($this, str()->camel($coluna))))
            ->whenNotEmpty(
                function (Collection $collection) use ($query) {
                    $collection->each(function (string $direcao, string $coluna) use ($query) {
                        $coluna = str()->camel($coluna);

                        if (method_exists($this, $coluna)) {
                            $this->{$coluna}($query, $direcao);
                        }
                    });
                },
                function () use ($query) {
                    $query->orderByStatus();
                }
            );

        $query->orderBy('id', 'desc');

        return $next($query);
    }

    /**
     * Aplica a ordenação pelo data de solicitação do processo.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function solicitadaEm(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('solicitada_em', $direcao);
    }

    /**
     * Aplica a ordenação pelo data de entrega do processo.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function entregueEm(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('entregue_em', $direcao);
    }

    /**
     * Aplica a ordenação pelo data de devolução do processo.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function devolvidaEm(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('devolvida_em', $direcao);
    }

    /**
     * Aplica a ordenação pelo status do tipo de entrega, isto é, se efetivada
     * por guia ou não.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function porGuia(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('por_guia', $direcao);
    }

    /**
     * Aplica a ordenação pelo número do processo.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function processoNumero(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('processos.numero', $direcao);
    }

    /**
     * Aplica a ordenação pela sigla do solicitante.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function solicitanteSigla(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('solicitantes.username', $direcao);
    }

    /**
     * Aplica a ordenação pela sigla do recebedor.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function recebedorSigla(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('recebedores.username', $direcao);
    }

    /**
     * Aplica a ordenação pela sigla do remetente.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function remetenteSigla(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('remetentes.username', $direcao);
    }

    /**
     * Aplica a ordenação pela sigla do rearquivador.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function rearquivadorSigla(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('rearquivadores.username', $direcao);
    }

    /**
     * Aplica a ordenação pela sigla da lotação destinatária.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function lotacaoDestinatariaSigla(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('destinatarias.sigla', $direcao);
    }
}
