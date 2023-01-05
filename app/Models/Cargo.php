<?php

namespace App\Models;

use FruiVita\Corporativo\Models\Cargo as CargoCorporativo;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Cargo extends CargoCorporativo
{
    /**
     * Relacionamento cargo (1:N) usuário.
     *
     * Usuários com determinado cargo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'cargo_id', 'id');
    }
}
