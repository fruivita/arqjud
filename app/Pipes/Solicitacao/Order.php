<?php

namespace App\Pipes\Solicitacao;

use App\Pipes\OrderBase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Pressupõe join com as tabelas pais se o critério de ordenação for por elas.
 *
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class Order extends OrderBase
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
    public function handle(Builder $query, \Closure $next)
    {
        collect(request()->query('order'))
            ->filter(fn (string $direcao, string $coluna) => (method_exists($this, str($coluna)->camel())))
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

        $query->orderBy($this->column, 'desc');

        return $next($query);
    }

    /**
     * Aplica a ordenação pela data de solicitação do processo.
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
     * Aplica a ordenação pela data de entrega do processo.
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
     * Aplica a ordenação pela data de devolução do processo.
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
     * Aplica a ordenação pelo username do solicitante.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function solicitanteUsername(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('solicitantes.username', $direcao);
    }

    /**
     * Aplica a ordenação pelo username do recebedor.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function recebedorUsername(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('recebedores.username', $direcao);
    }

    /**
     * Aplica a ordenação pelo username do remetente.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function remetenteUsername(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('remetentes.username', $direcao);
    }

    /**
     * Aplica a ordenação pelo username do rearquivador.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function rearquivadorUsername(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('rearquivadores.username', $direcao);
    }

    /**
     * Aplica a ordenação pela sigla da lotação (destino).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function destinoSigla(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('destinos.sigla', $direcao);
    }
}
