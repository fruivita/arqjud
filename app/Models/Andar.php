<?php

namespace App\Models;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Andar extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'andares';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero'];

    /**
     * Relacionamento andar (N:1) prédio.
     *
     * Prédio do andar.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function predio()
    {
        return $this->belongsTo(Predio::class, 'predio_id', 'id');
    }

    /**
     * Relacionamento andar (1:N) salas.
     *
     * Salas do andar.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function salas()
    {
        return $this->hasMany(Sala::class, 'andar_id', 'id');
    }

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * Pressupõe join com as tabelas:
     * - Localidades;
     * - Prédios.
     *
     * Colunas pesquisadas:
     * - nome da localidade;
     * - nome do prédio;
     * - número do andar;
     * - apelido do andar.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $termo
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $termo = "{$termo}%";

        $query->where('localidades.nome', 'like', $termo)
            ->orWhere('predios.nome', 'like', $termo)
            ->orWhere('andares.numero', 'like', $termo)
            ->orWhere('andares.apelido', 'like', $termo);
    }
}
