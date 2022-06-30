<?php

namespace App\Http\Livewire\Traits;

/**
 * Trait idealizada para agrupar a lógica de armazenamento das preferências do
 * usuário.
 *
 * A classe que a utiliza deve definir o array que conterá as preferências que
 * devem ser armazenadas. O armazenamento é feito em cache.
 *
 * Propriedade que deve ser definida: `public array $preferencias` com o índice
 * `por_pagina` obrigatório definidor da quantidade de registros por página.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 * @see https://laravel.com/docs/9.x/cache
 */
trait SalvaColunasDePreferencia
{
    /**
     * Define o valor da propriedade `$preferencias` como o valor que está em
     * cache ou, se não existir, como o próprio valor de inicialização da
     * propriedade.
     *
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @return void
     */
    public function mountSalvaColunasDePreferencia()
    {
        $this->preferencias = cache()->get($this->getChave(), $this->preferencias);
    }

    /**
     * Salva em cache, por um ano, as preferências do usuário.
     *
     * @return void
     */
    public function salvarPreferencia()
    {
        $this->validar();

        cache()->put(
            $this->getChave(),
            $this->preferencias,
            now()->addYear()
        );
    }

    /**
     * Chave usada para armazenar/recuperar o cache das preferências do
     * usuário.
     *
     * A chave é formada pela concatenação do username e do nome da classe.
     *
     * @return string
     */
    private function getChave()
    {
        return auth()->user()->username . class_basename($this);
    }

    /**
     * Valida a paginação escolhida pelo usuário
     *
     * @return void
     */
    private function validar()
    {
        $this->validateOnly(
            field: 'preferencias.por_pagina',
            rules: ['preferencias.por_pagina' => ['in:10,25,50,100']],
            attributes: ['preferencias.por_pagina' => __('Pagination')]
        );
    }
}
