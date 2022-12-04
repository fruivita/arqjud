<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Localidade extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'localidades';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['nome'];

    /**
     * Relacionamento localidade (1:N) prédios.
     *
     * Prédios da localidade.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function predios()
    {
        return $this->hasMany(Predio::class, 'localidade_id', 'id');
    }

    /**
     * Relacionamento localidade (1:N) caixas.
     *
     * Caixas criadas pela localidade.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function caixasCriadas()
    {
        return $this->hasMany(Caixa::class, 'localidade_criadora_id', 'id');
    }

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * Colunas pesquisadas:
     * - nome da localidade.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $termo
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $query->where('nome', 'like', "{$termo}%");
    }
}
