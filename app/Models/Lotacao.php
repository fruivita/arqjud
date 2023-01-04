<?php

namespace App\Models;

use FruiVita\Corporativo\Models\Lotacao as LotacaoCorporativo;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Lotacao extends LotacaoCorporativo
{
    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'por_guia' => 'boolean',
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
}
