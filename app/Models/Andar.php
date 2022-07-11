<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Andar extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'andares';

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
     * Todos os andares.
     *
     * Acompanhadas das seguintes colunas extras:
     * - localidade_id: id da localidade pai
     * - localidade_nome: nome da localidade pai
     * - predio_nome: nome do prédio pai
     * - salas_count: quantidade de salas do andar
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function hierarquia()
    {
        return
        self::join('predios', 'andares.predio_id', '=', 'predios.id')
        ->join('localidades', 'predios.localidade_id', '=', 'localidades.id')
        ->leftJoin('salas', 'salas.andar_id', '=', 'andares.id')
        ->select([
            'andares.*',
            'localidades.id as localidade_id',
            'localidades.nome as localidade_nome',
            'predios.nome as predio_nome',
            DB::raw('COUNT(salas.andar_id) as salas_count'),
        ])
        ->groupBy('andares.id');
    }

    /**
     * Campos hierarquizados do modelo.
     *
     * Chaves:
     * - localidade_id: id da localidade pai
     * - localidade_nome: nome da localidade pai
     * - predio_id: id do prédio pai
     * - predio_nome: nome do prédio pai
     * - salas_count: quantidade de salas do andar
     *
     * @return \Illuminate\Support\Collection
     */
    private function dadosHierarquicos()
    {
        $andar = isset($this->localidade_nome)
        ? $this
        : self::hierarquia()->find($this->id);

        return collect([
            'localidade_id' => $andar->localidade_id,
            'localidade_nome' => $andar->localidade_nome,
            'predio_id' => $andar->predio_id,
            'predio_nome' => $andar->predio_nome,
            'salas_count' => $andar->salas_count,
        ]);
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
        $dados_hierarquicos = $this->dadosHierarquicos();

        return collect([
            __('Localidade') => route('arquivamento.cadastro.localidade.edit', $dados_hierarquicos->get('localidade_id')),
            __('Prédio') => route('arquivamento.cadastro.predio.edit', $dados_hierarquicos->get('predio_id')),
        ])->when($root, function ($collection) {
            return $collection->put(__('Andar'), route('arquivamento.cadastro.andar.edit', $this->id));
        });
    }

    /**
     * Salva a sala informado como filho do andar. Também cria a estante e a
     * prateleira padrão.
     *
     * A estente e a prateleira padrão são as que não foram revisadas ou
     * criadas por uma requisição do usuário, mas automaticamente criadas pela
     * aplicação.
     *
     * @param \App\Models\Sala $sala
     *
     * @return bool
     */
    public function criarSala(Sala $sala)
    {
        try {
            DB::transaction(function () use ($sala) {
                $this->salas()->save($sala);

                $estante = Estante::modeloPadrao();

                $sala->estantes()->save($estante);

                $estante->prateleiras()->save(Prateleira::modeloPadrao());
            });

            return true;
        } catch (\Throwable $exception) {
            Log::error(
                __('Falha na criação da sala'),
                [
                    'andar' => $this,
                    'sala' => $sala,
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }

    /**
     * Ordenação padrão do modelo.
     *
     * Ordenação:
     * - 1º numero em ordem crescente
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
        return $query->orderBy('numero', 'asc');
    }

    /**
     * Andares do prédio informado.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id_predio
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDoPredio($query, int $id_predio)
    {
        return $query->where('predio_id', $id_predio);
    }
}
