<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Sala extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'salas';

    /**
     * Relacionamenta sala (N:1) andar.
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
     * Relacionamenta sala (1:N) estantes.
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
     * Todas os salas.
     *
     * Acompanhadas das seguintes colunas extras:
     * - localidade_id: id da localidade pai
     * - localidade_nome: nome da localidade pai
     * - predio_id: id do prédio pai
     * - predio_nome: nome do prédio pai
     * - andar_apelido: apelido do andar pai
     * - andar_numero: número do andar pai
     * - estantes_count: quantidade de estantes da sala
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function hierarquia()
    {
        return
        self::join('andares', 'salas.andar_id', '=', 'andares.id')
        ->join('predios', 'andares.predio_id', '=', 'predios.id')
        ->join('localidades', 'predios.localidade_id', '=', 'localidades.id')
        ->leftJoin('estantes', 'estantes.sala_id', '=', 'salas.id')
        ->select([
            'salas.*',
            'localidades.id as localidade_id',
            'localidades.nome as localidade_nome',
            'predios.id as predio_id',
            'predios.nome as predio_nome',
            'andares.apelido as andar_apelido',
            'andares.numero as andar_numero',
            DB::raw('COUNT(estantes.sala_id) as estantes_count')
        ])
        ->groupBy('salas.id');
    }

    /**
     * Campos hierarquizados do modelo.
     *
     * keys:
     * - localidade_id: id da localidade pai
     * - localidade_nome: nome da localidade pai
     * - predio_id: id do prédio pai
     * - predio_nome: nome do prédio pai
     * - andar_id: id do andar pai
     * - andar_numero: número do andar pai
     * - andar_apelido: apelido do andar pai
     * - estantes_count: quantidade de estantes da sala
     *
     * @return \Illuminate\Support\Collection
     */
    private function dadosHierarquicos()
    {
        $sala = isset($this->localidade_nome)
        ? $this
        : self::hierarquia()->find($this->id);

        return collect([
            'localidade_id' => $sala->localidade_id,
            'localidade_nome' => $sala->localidade_nome,
            'predio_id' => $sala->predio_id,
            'predio_nome' => $sala->predio_nome,
            'andar_id' => $sala->andar_id,
            'andar_apelido' => $sala->andar_apelido,
            'andar_numero' => $sala->andar_numero,
            'estantes_count' => $sala->estantes_count,
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
            __('Andar') => route('arquivamento.cadastro.andar.edit', $dados_hierarquicos->get('andar_id')),
        ])->when($root, function ($collection) {
            return $collection->put(__('Sala'), route('arquivamento.cadastro.sala.edit', $this->id));
        });
    }

    /**
     * Salva a estante informada como filha da sala. Também cria a prateleira
     * padrão.
     *
     * A prateleira padrão é a que não foi revisada ou criada por uma
     * requisição do usuário, mas automaticamente criada pela aplicação.
     *
     * @param \App\Models\Estante $estante
     *
     * @return bool
     */
    public function criarEstante(Estante $estante)
    {
        try {
            DB::transaction(function () use ($estante) {
                $this->estantes()->save($estante);

                $estante->prateleiras()->save(Prateleira::modeloPadrao());
            });

            return true;
        } catch (\Throwable $exception) {
            Log::error(
                __('Falha na criação da estante'),
                [
                    'sala' => $this,
                    'estante' => $estante,
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }
}
