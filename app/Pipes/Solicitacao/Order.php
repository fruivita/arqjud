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
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function processoNumero(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('processos.numero', $direcao);
    }

    /**
     * Aplica a ordenação pela matrícula do solicitante.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function solicitanteMatricula(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('solicitantes.matricula', $direcao);
    }

    /**
     * Aplica a ordenação pela matrícula do recebedor.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function recebedorMatricula(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('recebedores.matricula', $direcao);
    }

    /**
     * Aplica a ordenação pela matrícula do remetente.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function remetenteMatricula(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('remetentes.matricula', $direcao);
    }

    /**
     * Aplica a ordenação pela matrícula do rearquivador.
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function rearquivadorMatricula(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('rearquivadores.matricula', $direcao);
    }

    /**
     * Aplica a ordenação pela sigla da lotação (destino).
     *
     * @param  string  $direcao asc ou desc
     * @return void
     */
    protected function destinoSigla(Builder $query, string $direcao)
    {
        $direcao = ascOrDesc($direcao);

        $query->orderBy('destinos.sigla', $direcao);
    }
}
