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
class Estante extends Model
{
    use HasFactory;
    use ComHumanizacao;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'estantes';

    /**
     * Relacionamento estante (N:1) sala.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sala()
    {
        return $this->belongsTo(Sala::class, 'sala_id', 'id');
    }

    /**
     * Relacionamento estante (1:N) prateleiras.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prateleiras()
    {
        return $this->hasMany(Prateleira::class, 'estante_id', 'id');
    }

    /**
     * Todas as estantes.
     *
     * Acompanhadas das seguintes colunas extras:
     * - localidade_id: id da localidade pai
     * - localidade_nome: nome da localidade pai
     * - predio_id: id do prédio pai
     * - predio_nome: nome do prédio pai
     * - andar_id: id do andar pai
     * - andar_apelido: apelido do andar pai
     * - andar_numero: número do andar pai
     * - sala_numero: número da sala pai
     * - prateleiras_count: quantidade de prateleiras da estante
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function hierarquia()
    {
        return
        self::join('salas', 'estantes.sala_id', '=', 'salas.id')
        ->join('andares', 'salas.andar_id', '=', 'andares.id')
        ->join('predios', 'andares.predio_id', '=', 'predios.id')
        ->join('localidades', 'predios.localidade_id', '=', 'localidades.id')
        ->leftJoin('prateleiras', 'prateleiras.estante_id', '=', 'estantes.id')
        ->select([
            'estantes.*',
            'localidades.id as localidade_id',
            'localidades.nome as localidade_nome',
            'predios.id as predio_id',
            'predios.nome as predio_nome',
            'andares.id as andar_id',
            'andares.apelido as andar_apelido',
            'andares.numero as andar_numero',
            'salas.numero as sala_numero',
            DB::raw('COUNT(prateleiras.estante_id) as prateleiras_count')
        ])
        ->groupBy('estantes.id');
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
     * - prateleiras_count: quantidade de prateleiras da estante
     *
     * @return \Illuminate\Support\Collection
     */
    private function dadosHierarquicos()
    {
        $estante = isset($this->localidade_nome)
        ? $this
        : self::hierarquia()->find($this->id);

        return collect([
            'localidade_id' => $estante->localidade_id,
            'localidade_nome' => $estante->localidade_nome,
            'predio_id' => $estante->predio_id,
            'predio_nome' => $estante->predio_nome,
            'andar_id' => $estante->andar_id,
            'andar_apelido' => $estante->andar_apelido,
            'andar_numero' => $estante->andar_numero,
            'sala_id' => $estante->sala_id,
            'sala_numero' => $estante->sala_numero,
            'prateleiras_count' => $estante->prateleiras_count,
        ]);
    }

    /**
     * Estante em formato para leitura humana.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function paraHumano(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->humanizarEstante($this->numero)
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
        ])->when($root, function ($collection) {
            return $collection->put(__('Estante'), route('arquivamento.cadastro.estante.edit', $this->id));
        });
    }

    /**
     * Estante padrão para ser utilizada na criação da sala.
     *
     * @return self
     */
    public static function modeloPadrao()
    {
        $estante = new self();
        $estante->numero = 0;
        $estante->apelido = __('Não informada');
        $estante->descricao = __('Item provisório/padrão criado por sistema para eventual análise futura. Caso não seja um atributo obrigatório, pode ser ignorado');

        return $estante;
    }
}
