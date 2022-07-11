<?php

namespace App\Models;

use App\Enums\Permissao as EnumPermissao;
use FruiVita\Corporativo\Models\Usuario as UsuarioCorporativo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var string[]
     */
    protected $fillable = [
        'nome',
        'username',
        'password',
        'guid',
        'domain',
        'lotacao_id',
        'cargo_id',
        'funcao_confianca_id',
    ];

    /**
     * Os atributos que não estão sujeitos à serialização.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Os relacionamentos que sempre devem ser carregados.
     *
     * @var string[]
     */
    protected $with = ['perfil'];

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
     * Perfil antigo do usuário, isto é, antes da delagação. Util para retornar
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
     * Verifica se o perfil foi obtido por delegação ou se é um perfil
     * original.
     *
     * @return bool true se por delegação ou false caso contrário
     */
    public function perfilPorDelegacao()
    {
        return ! is_null($this->perfil_concedido_por);
    }

    /**
     * Usuário que delegou a permissão para outro.
     *
     * Relacionamento delegante (N:1) delegado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function delegante()
    {
        return $this->belongsTo(Usuario::class, 'perfil_concedido_por', 'id');
    }

    /**
     * Usuários com perfil delegados por outro.
     *
     * Relacionamento delegante (1:N) delegados.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function delegados()
    {
        return $this->hasMany(Usuario::class, 'perfil_concedido_por', 'id');
    }

    /**
     * Usuário autenticado em formato para leitura humana.
     *
     * @return string
     */
    public function paraHumano()
    {
        return $this->username;
    }

    /**
     * Delega o perfil para o usuário informado.
     *
     * @param \App\Models\Usuario $delegado
     *
     * @return bool
     */
    public function delegar(Usuario $delegado)
    {
        $delegado
            ->delegante()
            ->associate($this);
        $delegado
            ->perfilAntigo()
            ->associate($delegado->perfil);
        $delegado
            ->perfil()
            ->associate($this->perfil);

        return $delegado->save();
    }

    /**
     * Revoga a delegação e restaura o perfil antigo do usuário.
     *
     * @return bool
     */
    public function revogaDelegacao()
    {
        return $this
        ->perfil()->associate($this->antigo_perfil_id)
        ->perfilAntigo()->dissociate()
        ->delegante()->dissociate()
        ->save();
    }

    /**
     * Revoga as delegações feitas pelo usuário restaurando o perfil antigo de
     * cada usuário.
     *
     * @return void
     */
    private function revogaDelegacoes()
    {
        $this->delegados()->get()->each(function ($usuario) {
            $usuario->perfil_id = $usuario->antigo_perfil_id;
            $usuario->perfil_concedido_por = null;
            $usuario->antigo_perfil_id = null;
            $usuario->save();
        });
    }

    /**
     * Atualiza as propriedades do usuário e remove as suas delegações.
     *
     * @return bool
     */
    public function updateERevogaDelegacoes()
    {
        try {
            DB::beginTransaction();

            $this
            ->delegante()->dissociate()
            ->perfilAntigo()->dissociate()
            ->save();

            $this->revogaDelegacoes();

            DB::commit();

            return true;
        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error(
                __('Falha na atualização do usuário'),
                [
                    'usuario' => $this,
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }

    /**
     * Ordenação padrão do modelo.
     *
     * Ordenação:
     * - 1º name em ordem alfabética asc
     * - 2º nome com valor null
     * - 3º username asc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @see https://learnsql.com/blog/how-to-order-rows-with-nulls/
     */
    public function scopeOrdenacaoPadrao(Builder $query)
    {
        return $query
                ->orderByRaw('nome IS NULL')
                ->orderBy('nome', 'asc')
                ->orderBy('username', 'asc');
    }

    /**
     * Usuários passíveis de delegação.
     *
     * Notar que pode a delegação não ser possível por outro motivo. Ex.:
     * perfil superior, etc. Contudo, usuários de uma mesma lotação poderiam,
     * em tese, delegar seu perfil para outro. Esse escopo, não se preocupa com
     * esses filigramas, retornando tão somente os usuários da mesma lotação.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @see https://learnsql.com/blog/how-to-order-rows-with-nulls/
     */
    public function scopeDelegaveis(Builder $query)
    {
        return $query->where('lotacao_id', auth()->user()->lotacao_id);
    }

    /**
     * Verifica se o usuário autenticado é um super administrador.
     *
     * @return bool
     */
    public function eSuperAdmin()
    {
        return once(function () {
            $configuracao = Configuracao::find(Configuracao::ID);

            return
                isset($configuracao)
                && $configuracao->superadmin === $this->username
                ? true
                : false;
        });
    }

    /**
     * Id de todas as permissões do usuário.
     *
     * @return \Illuminate\Support\Collection
     */
    public function permissoes()
    {
        return once(function () {
            if ($this->perfil === null) {
                $this->refresh();
            }

            $this->load(['perfil.permissoes' => function ($query) {
                $query->select('id');
            }]);

            return $this->perfil->permissoes->pluck('id');
        });
    }

    /**
     * Verifica se o usuário possui a permissão informada.
     *
     * @param \App\Enums\Permissao $permissao
     *
     * @return bool
     */
    public function possuiPermissao(EnumPermissao $permissao)
    {
        return $this
            ->permissoes()
            ->contains($permissao->value);
    }
}
