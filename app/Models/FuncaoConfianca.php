<?php

namespace App\Models;

use FruiVita\Corporativo\Models\FuncaoConfianca as FuncaoConfiancaCorporativo;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class FuncaoConfianca extends FuncaoConfiancaCorporativo
{
    /**
     * Relacionamento função de confiança (1:N) usuário.
     *
     * Usuários com determinada função de confiança.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'funcao_confianca_id', 'id');
    }
}
