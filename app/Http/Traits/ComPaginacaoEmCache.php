<?php

namespace App\Http\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

/**
 * Trait para operações comuns com paginação persistida em cache.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait ComPaginacaoEmCache
{
    /**
     * Opções possíveis para paginação.
     *
     * A primeira posição é a paginação default, isto é, a quantidade de itens
     * por página padrão.
     *
     * @var array
     */
    private $opcoes = [10, 25, 50, 100];

    /**
     * Opções possíveis para paginação.
     *
     * A primeira posição é a paginação default, isto é, a quantidade de itens
     * por página padrão.
     *
     * @return array
     */
    public function getOpcoes()
    {
        return $this->opcoes;
    }

    /**
     * Opção padrão de paginação.
     *
     * @return int
     */
    public function paginacaoPadrao()
    {
        return Arr::first($this->opcoes);
    }

    /**
     * Chave de armazenamento da paginação no array de preferências utilizando
     * dot notation.
     *
     * @return string
     */
    private function chave()
    {
        return 'per_page.' . Request::path();
    }

    /**
     * Define e retorna a quantidade de itens por página que deve ser utilizada
     * de acordo com as preferências do usuário para a página visitada.
     *
     * A preferência do usuário é persistida em cache e extraída do request.
     *
     * @return int
     */
    public function perPage()
    {
        $chave = $this->chave();
        $escolha = $this->paginacaoPadrao();

        $cached_preferencias = $this->preferenciasEmCache();
        $cached_per_page = Arr::get($cached_preferencias, $chave, null);

        $request_per_page = request()->integer('per_page');
        if (in_array($request_per_page, $this->opcoes)) {
            // Paginação informada no request é válida? use-a em vez da padrão.
            $escolha = $request_per_page;
        } elseif (in_array($cached_per_page, $this->opcoes)) {
            // Paginação em cache é válida? use-a em vez da padrão.
            $escolha = $cached_per_page;
        }

        Arr::set($cached_preferencias, $chave, $escolha);

        $this->saveCache($cached_preferencias);

        return $escolha;
    }

    /**
     * Preferências persistidas em cache.
     *
     * @return array<string, mixed> $preferencias
     */
    private function preferenciasEmCache()
    {
        return Cache::get(auth()->user()->username, []);
    }

    /**
     * Faz a persistência em cache das preferências.
     *
     * @param  array<string, mixed>  $preferencias
     * @return bool
     */
    private function saveCache(array $preferencias)
    {
        return Cache::forever(
            auth()->user()->username,
            $preferencias
        );
    }
}
