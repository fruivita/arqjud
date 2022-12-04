<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Guia extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'guias';

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'remetente' => AsCollection::class,
        'recebedor' => AsCollection::class,
        'lotacao_destinataria' => AsCollection::class,
        'processos' => AsCollection::class,
    ];

    /**
     * Relacionamento guia (1:N) solicitações.
     *
     * Solicitações registradas na guia.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitacoes()
    {
        return $this->hasMany(Solicitacao::class, 'guia_id', 'id');
    }
}
