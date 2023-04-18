<?php

namespace App\Models;

use App\Models\Trait\Auditavel;
use FruiVita\Corporativo\Models\Cargo as CargoCorporativo;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Cargo extends CargoCorporativo
{
    use Auditavel;

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
