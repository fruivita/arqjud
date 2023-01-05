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
}
