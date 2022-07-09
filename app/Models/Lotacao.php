<?php

namespace App\Models;

use FruiVita\Corporativo\Models\Lotacao as LotacaoCorporativo;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Lotacao extends LotacaoCorporativo
{
    /**
     * ID da lotação dos usuários sem lotação. Em regra, são usuários que
     * exitem apenas no servidor LDAP.
     *
     * @var int
     */
    public const SEM_LOTACAO = 0;
}
