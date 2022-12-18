<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
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
        'gerada_em' => 'datetime',
        'solicitante' => AsArrayObject::class,
        'remetente' => AsArrayObject::class,
        'recebedor' => AsArrayObject::class,
        'lotacao_destinataria' => AsArrayObject::class,
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

    /**
     * Próximo número da guia disponível para o ano informado ou para o atual.
     *
     * @param  int|null  $ano
     * @return int
     */
    public static function proximoNumero(int $ano = null)
    {
        return intval(Guia::where('ano', $ano ?? now()->year)->max('numero')) + 1;
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
            $query->where('numero', 'like', $termo)
                ->orWhere('ano', 'like', $termo);
        });
    }
}
