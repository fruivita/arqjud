<?php

namespace App\Models;

use App\Models\Traits\ComHumanizacao;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Prateleira extends Model
{
    use ComHumanizacao;
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'prateleiras';

    /**
     * Relacionamento prateleira (N:1) estante.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estante()
    {
        return $this->belongsTo(Estante::class, 'estante_id', 'id');
    }

    /**
     * Relacionamento prateleira (1:N) caixas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function caixas()
    {
        return $this->hasMany(Caixa::class, 'prateleira_id', 'id');
    }

    /**
     * Todas as prateleiras.
     *
     * Acompanhadas das seguintes colunas extras:
     * - localidade_id: id da localidade pai
     * - localidade_nome: nome da localidade pai
     * - predio_id: id do prédio pai
     * - predio_nome: nome do prédio pai
     * - andar_id: id do andar pai
     * - andar_apelido: apelido do andar pai
     * - andar_numero: número do andar pai
     * - sala_id: id da sala pai
     * - sala_numero: número da sala pai
     * - estante_apelido: apelido da estante pai
     * - estante_numero: número da estante pai
     * - caixas_count: quantidade de caixas da prateleira
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function hierarquia()
    {
        return
        self::join('estantes', 'prateleiras.estante_id', '=', 'estantes.id')
        ->join('salas', 'estantes.sala_id', '=', 'salas.id')
        ->join('andares', 'salas.andar_id', '=', 'andares.id')
        ->join('predios', 'andares.predio_id', '=', 'predios.id')
        ->join('localidades', 'predios.localidade_id', '=', 'localidades.id')
        ->leftJoin('caixas', 'caixas.prateleira_id', '=', 'prateleiras.id')
        ->select([
            'prateleiras.*',
            'localidades.id as localidade_id',
            'localidades.nome as localidade_nome',
            'predios.id as predio_id',
            'predios.nome as predio_nome',
            'andares.id as andar_id',
            'andares.apelido as andar_apelido',
            'andares.numero as andar_numero',
            'salas.id as sala_id',
            'salas.numero as sala_numero',
            'estantes.apelido as estante_apelido',
            'estantes.numero as estante_numero',
            DB::raw('COUNT(caixas.prateleira_id) as caixas_count'),
        ])
        ->groupBy('prateleiras.id');
    }

    /**
     * Campos hierarquizados do modelo.
     *
     * Chaves:
     * - localidade_id: id da localidade pai
     * - localidade_nome: nome da localidade pai
     * - predio_id: id do prédio pai
     * - predio_nome: nome do prédio pai
     * - andar_id: id do andar pai
     * - andar_apelido: apelido do andar pai
     * - andar_numero: número do andar pai
     * - sala_id: id da sala pai
     * - sala_numero: número da sala pai
     * - estante_id: id da estante pai
     * - estante_apelido: apelido da estante pai
     * - estante_numero: número da estante pai
     * - caixas_count: quantidade de caixas da prateleira
     *
     * @return \Illuminate\Support\Collection
     */
    private function dadosHierarquicos()
    {
        $prateleira = isset($this->localidade_nome)
        ? $this
        : self::hierarquia()->find($this->id);

        return collect([
            'localidade_id' => $prateleira->localidade_id,
            'localidade_nome' => $prateleira->localidade_nome,
            'predio_id' => $prateleira->predio_id,
            'predio_nome' => $prateleira->predio_nome,
            'andar_id' => $prateleira->andar_id,
            'andar_apelido' => $prateleira->andar_apelido,
            'andar_numero' => $prateleira->andar_numero,
            'sala_id' => $prateleira->sala_id,
            'sala_numero' => $prateleira->sala_numero,
            'estante_id' => $prateleira->estante_id,
            'estante_apelido' => $prateleira->estante_apelido,
            'estante_numero' => $prateleira->estante_numero,
            'caixas_count' => $prateleira->caixas_count,
        ]);
    }

    /**
     * Prateleira em formato para leitura humana.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function paraHumano(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->humanizarPrateleira($this->numero)
        );
    }

    /**
     * Estante em formato para leitura humana.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function estanteParaHumano(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->humanizarEstante($this->estante_numero)
        );
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
            __('Sala') => route('arquivamento.cadastro.sala.edit', $dados_hierarquicos->get('sala_id')),
            __('Estante') => route('arquivamento.cadastro.estante.edit', $dados_hierarquicos->get('estante_id')),
        ])->when($root, function ($collection) {
            return $collection->put(__('Prateleira'), route('arquivamento.cadastro.prateleira.edit', $this->id));
        });
    }

    /**
     * Prateleira padrão para ser utilizada na criação da estante.
     *
     * @return self
     */
    public static function modeloPadrao()
    {
        $prateleira = new self();
        $prateleira->numero = 0;
        $prateleira->apelido = __('Não informada');
        $prateleira->descricao = __('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado');

        return $prateleira;
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
     * Prateleiras da estante informada.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int                                   $id_estante
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDaEstante($query, int $id_estante)
    {
        return $query->where('estante_id', $id_estante);
    }
}
