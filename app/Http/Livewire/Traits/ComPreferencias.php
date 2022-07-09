<?php

namespace App\Http\Livewire\Traits;

use Illuminate\Support\Arr;

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
trait ComPreferencias
{
    /**
     * Inicializa a propriedade `$preferencias` com o valor em cache ou, se não
     * existir cache, com o próprio valor de inicialização da propriedade.
     *
     * Executado uma única vez, imediatamente após o componente ser
     * instanciado, mas antes do método render() ser acionado. É acionado
     * apenas no carregamento inicial da página e nunca mais chamado, inclusive
     * nas atualizações do componente.
     *
     * @return void
     */
    public function mountComPreferencias()
    {
        $this->preferencias = cache()->get($this->getChave(), $this->preferencias);
    }

    /**
     * Salva em cache, pela quantidade de meses informada, as preferências do
     * usuário.
     *
     * @param int $meses
     *
     * @return void
     */
    public function salvarPreferencia(int $meses = 12)
    {
        $this->validar();

        if ($meses <= 0) {
            $meses = 12;
        }

        cache()->put(
            $this->getChave(),
            $this->preferencias,
            now()->addMonths($meses)
        );
    }

    /**
     * Chave usada para armazenar e recuperar o cache das preferências do
     * usuário.
     *
     * A chave é formada pela concatenação do termo 'preferencias', do username
     * e do nome da classe.
     *
     * @return string
     */
    private function getChave()
    {
        return Arr::join(
            [
                'preferencias',
                auth()->user()->username,
                class_basename($this)
            ],
            '-'
        );
    }

    /**
     * Valida a paginação definida pelo usuário.
     *
     * @return void
     */
    private function validar()
    {
        $this->validateOnly(
            field: 'preferencias.por_pagina',
            rules: ['preferencias.por_pagina' => ['in:10,25,50,100']],
            attributes: ['preferencias.por_pagina' => __('Paginação')]
        );
    }
}
