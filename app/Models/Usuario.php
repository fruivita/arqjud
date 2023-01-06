<?php

namespace App\Models;

use FruiVita\Corporativo\Models\Usuario as UsuarioCorporativo;
use Illuminate\Database\Eloquent\Builder;
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
     * Relacionamento usuário (N:1) lotação.
     *
     * Lotação de determinado usuário.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lotacao()
    {
        return $this->belongsTo(Lotacao::class, 'lotacao_id', 'id');
    }

    /**
     * Relacionamento usuário (N:1) cargo.
     *
     * Cargo de determinado usuário.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id', 'id');
    }

    /**
     * Função de confiança de determinado usuário.
     *
     * Relacionamento usuário (N:1) função de confiança.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function funcaoConfianca()
    {
        return $this->belongsTo(FuncaoConfianca::class, 'funcao_confianca_id', 'id');
    }

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
     * Verifica se o usuário pertence a uma lotação administrável.
     *
     * @return bool
     */
    public function pertenceLotacaoAdministravel()
    {
        return Lotacao::administraveis()->contains('id', $this->lotacao_id);
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

    /**
     * Determinada se o usuário está habilitado a interagir com a remessa de
     * processos, isto é, se possui todos as propriedades mínimas.
     *
     * O usuário é considerado habilitado quando presentes, validamente, as
     * propriedades abaixo:
     * - Nome;
     * - Matrícula;
     * - Username;
     * - Email;
     * - Lotação.
     *
     * @return bool
     */
    public function habilitado()
    {
        return
            !empty($this->nome)
            && !empty($this->matricula)
            && !empty($this->username)
            && !empty($this->email)
            && $this->lotacao_id >= 1;
    }

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $termo
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $termo = "{$termo}%";

        $query->where(function (Builder $query) use ($termo) {
            $query->where('usuarios.nome', 'like', $termo)
                ->orWhere('usuarios.matricula', 'like', $termo)
                ->orWhere('usuarios.username', 'like', $termo)
                ->orWhere('usuarios.email', 'like', $termo)
                ->orWhere('lotacoes.sigla', 'like', $termo)
                ->orWhere('lotacoes.nome', 'like', $termo)
                ->orWhere('cargos.nome', 'like', $termo)
                ->orWhere('funcoes_confianca.nome', 'like', $termo)
                ->orWhere('perfis.nome', 'like', $termo);
        });
    }

    /**
     * Verifica se o perfil do usuário possuir maior poder que o do usuário
     * informado.
     *
     * Usuário sem perfil é sempre considerado inferior.
     *
     * @param  \App\Models\Usuario  $usuario
     * @return bool
     */
    public function perfilSuperior(Usuario $usuario)
    {
        $this->loadMissing('perfil');
        $usuario->loadMissing('perfil');

        return
            empty($usuario->perfil)
            || $this->perfil->poder > $usuario->perfil->poder;
    }
}
