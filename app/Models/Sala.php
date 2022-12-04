<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Builder;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Sala extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'salas';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero'];

    /**
     * Relacionamento sala (N:1) andar.
     *
     * Andar da sala.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function andar()
    {
        return $this->belongsTo(Andar::class, 'andar_id', 'id');
    }

    /**
     * Relacionamento sala (1:N) estantes.
     *
     * Estantes da sala.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function estantes()
    {
        return $this->hasMany(Estante::class, 'sala_id', 'id');
    }

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * Pressupõe join com as tabelas:
     * - Localidades;
     * - Prédios;
     * - Andares.
     *
     * Colunas pesquisadas:
     * - nome da localidade;
     * - nome do prédio;
     * - número do andar;
     * - apelido do andar;
     * - número da sala.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $termo
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $termo = "{$termo}%";

        $query->where('localidades.nome', 'like', $termo)
            ->orWhere('predios.nome', 'like', $termo)
            ->orWhere('andares.numero', 'like', $termo)
            ->orWhere('andares.apelido', 'like', $termo)
            ->orWhere('salas.numero', 'like', $termo);
    }
}
