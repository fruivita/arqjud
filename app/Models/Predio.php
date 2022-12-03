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
     * Colunas pesquisadas:
     * - nome da localidade
     * - nome do prédio
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $termo
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $termo = "{$termo}%";

        $query->where(function ($query) use ($termo) {
            $query->where('nome', 'like', $termo)
                ->orWhereIn(
                    'localidade_id',
                    Localidade::query()
                        ->where('nome', 'like', $termo)
                        ->pluck('id')
                );
        });

        // $query->whereIn('id', function (Builder $query) use ($termo) {
        //     $query->select('id')
        //         ->from(function ($query) use ($termo) {
        //             $query->select('id')
        //                 ->from('predios')
        //                 ->where('nome', 'like', "{$termo}%")
        //                 ->union(
        //                     $query->newQuery()
        //                         ->select('predios.id')
        //                         ->from('predios')
        //                         ->join('localidades', 'localidades.id', 'predios.localidade_id')
        //                         ->where('localidades.nome', 'like', "{$termo}%")

        //                 );
        //         }, 'matches');
        // });
    }
}
