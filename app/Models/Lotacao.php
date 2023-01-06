<?php

namespace App\Models;

use FruiVita\Corporativo\Models\Lotacao as LotacaoCorporativo;
use Illuminate\Database\Eloquent\Builder;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Lotacao extends LotacaoCorporativo
{
    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'administravel' => 'boolean',
    ];

    /**
     * Relacionamento lotação filha (N:1) lotação pai.
     *
     * Lotação pai de uma determinada lotação.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lotacaoPai()
    {
        return $this->belongsTo(Lotacao::class, 'lotacao_pai', 'id');
    }

    /**
     * Relacionamento lotação pai (1:N) lotações filhas.
     *
     * Lotações filhas de uma determinada lotação.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lotacoesFilhas()
    {
        return $this->hasMany(Lotacao::class, 'lotacao_pai', 'id');
    }

    /**
     * Relacionamento lotação (1:N) usuario.
     *
     * Usuários lotados em uma determinada lotação.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'lotacao_id', 'id');
    }

    /**
     * Relacionamento lotação destinatária (1:N) solicitações.
     *
     * Solicitações de processo destinados à lotação.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitacoes()
    {
        return $this->hasMany(Solicitacao::class, 'lotacao_destinataria_id', 'id');
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
            $query->where('lotacoes.sigla', 'like', $termo)
                ->orWhere('lotacoes.nome', 'like', $termo)
                ->orWhere('lotacoes_pai.sigla', 'like', $termo)
                ->orWhere('lotacoes_pai.nome', 'like', $termo);
        });
    }

    /**
     * Lotações administraveis.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function administraveis()
    {
        return once(function () {
            return self::where('administravel', true)->get();
        });
    }
}
