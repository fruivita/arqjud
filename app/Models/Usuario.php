<?php

namespace App\Models;

use FruiVita\Corporativo\Models\Usuario as UsuarioCorporativo;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 * @see https://ldaprecord.com/docs/laravel/v2/auth/database
 */
class Usuario extends UsuarioCorporativo implements LdapAuthenticatable
{
    use AuthenticatesWithLdap;
    use Notifiable;

    /**
     * {@inheritdoc}
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relacionamento usuário (N:1) perfil.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id', 'id');
    }

    /**
     * Perfil antigo do usuário, isto é, antes da delagação. Útil para retornar
     * o usuário ao seu antigo perfil.
     *
     * Relacionamento usuário (N:1) perfil.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function perfilAntigo()
    {
        return $this->belongsTo(Perfil::class, 'antigo_perfil_id', 'id');
    }

    /**
     * Usuário que delegou a permissão para outro.
     *
     * Relacionamento usuário delegante (N:1) usuário delegado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function delegante()
    {
        return $this->belongsTo(Usuario::class, 'perfil_concedido_por', 'id');
    }

    /**
     * Usuários com perfil delegado por outro.
     *
     * Relacionamento usuário delegante (1:N) usuário delegados.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function delegados()
    {
        return $this->hasMany(Usuario::class, 'perfil_concedido_por', 'id');
    }
}
