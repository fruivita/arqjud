<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class TipoProcesso extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'tipos_processo';

    /**
     * Relacionamento tipo_processo (1:N) caixas.
     *
     * Caixas do tipo de processo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function caixas()
    {
        return $this->hasMany(Caixa::class, 'tipo_processo_id', 'id');
    }

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $query->where('nome', 'like', "{$termo}%");
    }
}
