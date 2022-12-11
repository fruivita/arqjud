<?php

namespace App\Models;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Predio extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'predios';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['nome'];

    /**
     * Relacionamento prédio (N:1) localidade.
     *
     * Localidade do prédio.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function localidade()
    {
        return $this->belongsTo(Localidade::class, 'localidade_id', 'id');
    }

    /**
     * Relacionamento prédio (1:N) andares.
     *
     * Andares do prédio.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function andares()
    {
        return $this->hasMany(Andar::class, 'predio_id', 'id');
    }

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * Pressupõe join com a tabela:
     * - localidades.
     *
     * Colunas pesquisadas:
     * - nome da localidade;
     * - nome do prédio.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $termo
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $termo = "{$termo}%";

        $query->where('localidades.nome', 'like', $termo)
            ->orWhere('predios.nome', 'like', $termo);
    }
}
