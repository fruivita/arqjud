<?php

namespace App\Services\Menu;

/**
 * @see https://m.dotdev.co/design-pattern-service-layer-with-laravel-5-740ff0a7b65f
 * @see https://blackdeerdev.com/laravel-services-pattern/
 */
interface MenuInterface
{
    /**
     * Gera o menu de acordo com as permissões do usuário autenticado.
     *
     * Ex.:
     * [
     *      'nome' => 'cadastro',
     *      'links' => [
     *          'icone' => 'person',
     *          'href' => 'http://exemplo.com/algo',
     *          'texto' => 'Pessoas',
     *          'ativo' => false/true,
     *      ]
     * ]
     *
     * @return array
     */
    public function gerar();
}
