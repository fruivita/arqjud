<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Predio extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'predios';

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
     * Todos os prédios.
     *
     * Acompanhadas das seguintes colunas extras:
     * - localidade_nome: nome da localidade pai
     * - andares_count: quantidade de andares do prédio
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function hierarquia()
    {
        return
        self::join('localidades', 'predios.localidade_id', '=', 'localidades.id')
        ->leftJoin('andares', 'andares.predio_id', '=', 'predios.id')
        ->select([
            'predios.*',
            'localidades.nome as localidade_nome',
            DB::raw('COUNT(andares.predio_id) as andares_count'),
        ])
        ->groupBy('predios.id');
    }

    /**
     * Links para as entidades pai.
     *
     * @param bool $root deve incluir o elemento root?
     *
     * @return \Illuminate\Support\Collection
     */
    public function linksPais(bool $root)
    {
        return collect([
            __('Localidade') => route('arquivamento.cadastro.localidade.edit', $this->localidade_id),
        ])->when($root, function ($collection) {
            return $collection->put(__('Prédio'), route('arquivamento.cadastro.predio.edit', $this->id));
        });
    }

    /**
     * Ordenação padrão do modelo.
     *
     * Ordenação:
     * - 1º nome em ordem alfabética asc
     *
     * Especialmente útil para a popular combos, pois traz a ordenação mais
     * humanamente natural.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdenacaoPadrao($query)
    {
        return $query->orderBy('nome', 'asc');
    }

    /**
     * Prédios da localidade informada.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id_localidade
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDaLocalidade($query, int $id_localidade)
    {
        return $query->where('localidade_id', $id_localidade);
    }
}
