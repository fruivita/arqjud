<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Localidade extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'localidades';

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
     * Todas as localidades.
     *
     * Acompanhadas das seguintes colunas extras:
     * - predios_count: quantidade de prédios da localidade
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function hierarquia()
    {
        return
        self::leftJoin('predios', 'predios.localidade_id', '=', 'localidades.id')
        ->select([
            'localidades.*',
            DB::raw('COUNT(predios.localidade_id) as predios_count'),
        ])
        ->groupBy('localidades.id');
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
        return collect()->when($root, function ($collection) {
            return $collection->put(__('Localidade'), route('arquivamento.cadastro.localidade.edit', $this->id));
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
}
