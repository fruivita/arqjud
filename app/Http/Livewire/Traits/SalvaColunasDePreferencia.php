<?php

namespace App\Http\Livewire\Traits;

use Illuminate\Support\Facades\Validator;

/**
 * Trait idealizada para agrupar a lógica de armazenamento das preferências do
 * usuário relativas à exibição das tabelas.
 *
 * Mais especificamente, armazena as colunas da tabela que devem ser exibidas
 * ou ocultadas. O armazenamento é feito em cache.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 * @see https://laravel.com/docs/9.x/cache
 */
trait SalvaColunasDePreferencia
{
    /**
     * Inicializa o valor da propriedade `$colunas` com o valor que está em
     * cache ou, se não existir, com o próprio valor da propriedade.
     *
     * Presume a criação da propriedade `$colunas` do tipo array de strings no
     * componente.
     *
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @return void
     */
    public function mountSalvaColunasDePreferencia()
    {
        $this->colunas = cache()->get($this->getChave(), $this->colunas);
    }

    /**
     * Salva em cache, por um ano, as preferências do usuário.
     *
     * @return void
     */
    public function salvarPreferencia()
    {
        cache()->put(
            $this->getChave(),
            $this->colunas,
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
}
