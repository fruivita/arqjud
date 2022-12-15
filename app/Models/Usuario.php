<?php

namespace App\Models;

use FruiVita\Corporativo\Models\Usuario as UsuarioCorporativo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
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

    /**
     * Relacionamento usuário (solicitante) (1:N) solicitações.
     *
     * Solicitações de processo solicitadas pelo usuário.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitacoesSolicitadas()
    {
        return $this->hasMany(Solicitacao::class, 'solicitante_id', 'id');
    }

    /**
     * Relacionamento usuário (recebedor) (1:N) solicitações.
     *
     * Solicitações de processo entregues ao usuário.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitacoesRecebidas()
    {
        return $this->hasMany(Solicitacao::class, 'recebedor_id', 'id');
    }

    /**
     * Relacionamento usuário (remetente) (1:N) solicitações.
     *
     * Solicitações de processo remetidas pelo usuário.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitacoesRemetidas()
    {
        return $this->hasMany(Solicitacao::class, 'remetente_id', 'id');
    }

    /**
     * Relacionamento usuário (rearquivador) (1:N) solicitações.
     *
     * Solicitações de processo rearquivadas pelo usuário.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitacoesRearquivadas()
    {
        return $this->hasMany(Solicitacao::class, 'rearquivador_id', 'id');
    }

    /**
     * Slug de todas as permissões do usuário.
     *
     * @return \Illuminate\Support\Collection
     */
    private function permissoes()
    {
        return once(function () {
            $this->load(['perfil.permissoes' => function (BelongsToMany $query) {
                $query->select(['slug']);
            }]);

            return empty($this->perfil)
                ? collect([])
                : $this->perfil->permissoes->pluck('slug');
        });
    }

    /**
     * Verifica se o usuário possui a permissão informada.
     *
     * @param  string  $slug
     * @return bool
     */
    public function possuiPermissao(string $slug)
    {
        return $this
            ->permissoes()
            ->contains($slug);
    }

    /**
     * Usuários com o perfil operador.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOperadores($query)
    {
        return $query->whereRelation('perfil', 'slug', '=', Perfil::OPERADOR);
    }

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail(Notification $notification)
    {
        return $this->email;
    }
}
