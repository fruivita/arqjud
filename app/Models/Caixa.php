<?php

namespace App\Models;

use App\Models\Traits\ComHumanizacao;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Caixa extends Model
{
    use HasFactory;
    use ComHumanizacao;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'caixas';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var string[]
     */
    protected $fillable = ['ano', 'numero', 'descricao'];

    /**
     * Relacionamento caixa (N:1) prateleira.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prateleira()
    {
        return $this->belongsTo(Prateleira::class, 'prateleira_id', 'id');
    }

    /**
     * Relacionamento caixa (1:N) volumes da caixa.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function volumes()
    {
        return $this->hasMany(VolumeCaixa::class, 'caixa_id', 'id');
    }

    /**
     * Todas as caixas.
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
     * - estante_id: id da estante pai
     * - estante_apelido: apelido da estante pai
     * - estante_numero: número da estante pai
     * - prateleira_apelido: apelido da prateleira pai
     * - prateleira_numero: número da prateleira pai
     * - volumes_count: quantidade de volumes da caixa
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function hierarquia()
    {
        return
        self::join('prateleiras', 'caixas.prateleira_id', '=', 'prateleiras.id')
        ->join('estantes', 'prateleiras.estante_id', '=', 'estantes.id')
        ->join('salas', 'estantes.sala_id', '=', 'salas.id')
        ->join('andares', 'salas.andar_id', '=', 'andares.id')
        ->join('predios', 'andares.predio_id', '=', 'predios.id')
        ->join('localidades', 'predios.localidade_id', '=', 'localidades.id')
        ->leftJoin('volumes_caixa', 'volumes_caixa.caixa_id', '=', 'caixas.id')
        ->select([
            'caixas.*',
            'localidades.id as localidade_id',
            'localidades.nome as localidade_nome',
            'predios.id as predio_id',
            'predios.nome as predio_nome',
            'andares.id as andar_id',
            'andares.apelido as andar_apelido',
            'andares.numero as andar_numero',
            'salas.id as sala_id',
            'salas.numero as sala_numero',
            'estantes.id as estante_id',
            'estantes.apelido as estante_apelido',
            'estantes.numero as estante_numero',
            'prateleiras.apelido as prateleira_apelido',
            'prateleiras.numero as prateleira_numero',
            DB::raw('COUNT(volumes_caixa.caixa_id) as volumes_count')
        ])
        ->groupBy('caixas.id');
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
     * - prateleira_id: id da prateleira pai
     * - prateleira_apelido: apelido da prateleira pai
     * - prateleira_numero: número da prateleira pai
     * - volumes_count: quantidade de volumes da caixa
     *
     * @return \Illuminate\Support\Collection
     */
    private function dadosHierarquicos()
    {
        $caixa = isset($this->localidade_nome)
        ? $this
        : self::hierarquia()->find($this->id);

        return collect([
            'localidade_id' => $caixa->localidade_id,
            'localidade_nome' => $caixa->localidade_nome,
            'predio_id' => $caixa->predio_id,
            'predio_nome' => $caixa->predio_nome,
            'andar_id' => $caixa->andar_id,
            'andar_apelido' => $caixa->andar_apelido,
            'andar_numero' => $caixa->andar_numero,
            'sala_id' => $caixa->sala_id,
            'sala_numero' => $caixa->sala_numero,
            'estante_id' => $caixa->estante_id,
            'estante_apelido' => $caixa->estante_apelido,
            'estante_numero' => $caixa->estante_numero,
            'prateleira_id' => $caixa->prateleira_id,
            'prateleira_apelido' => $caixa->prateleira_apelido,
            'prateleira_numero' => $caixa->prateleira_numero,
            'volumes_count' => $caixa->volumes_count,
        ]);
    }

    /**
     * Caixa em formato para leitura humana.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function paraHumano(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->humanizarCaixa($this->numero, $this->ano)
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
     * Prateleira em formato para leitura humana.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function prateleiraParaHumano(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->humanizarPrateleira($this->prateleira_numero)
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
            __('Prateleira') => route('arquivamento.cadastro.prateleira.edit', $dados_hierarquicos->get('prateleira_id')),
        ])->when($root, function ($collection) {
            return $collection->put(__('Caixa'), route('arquivamento.cadastro.caixa.edit', $this->id));
        });
    }

    /**
     * Gera o próximo número da caixa disponível.
     *
     * @return int
     */
    public static function proximoNumeroCaixa(int $ano)
    {
        return self::where('ano', $ano)->max('numero') + 1;
    }

    /**
     * Gera o próximo número do volume da caixa disponível.
     *
     * @return int
     */
    public function proximoNumeroVolume()
    {
        return $this->volumes()->max('numero') + 1;
    }

    /**
     * Cria diversas caixas como filhas da prateleira informada e as persiste
     * no banco de dados.
     *
     * @param \App\Models\Caixa      $template   template para a criação das
     *                               caixas
     * @param int                    $quantidade número de caixas para criação
     * @param int                    $volumes    número de volumes das caixas
     * @param \App\Models\Prateleira $prateleira prateleira pai
     *
     * @return bool
     */
    public static function criarMuitas(Caixa $template, int $quantidade, int $volumes, Prateleira $prateleira)
    {
        try {
            DB::transaction(function () use ($template, $quantidade, $volumes, $prateleira) {
                $caixas = self::gerarMuitas($template, $quantidade, $prateleira);

                self::insert($caixas->toArray());

                $caixas_id = self::ultimosIdsCadastrados($caixas);

                VolumeCaixa::insert(
                    self::gerarVolumes($volumes, $caixas_id)->toArray()
                );
            });

            return true;
        } catch (\Throwable $exception) {
            Log::error(
                __('Falha na criação da caixa'),
                [
                    'template' => $template,
                    'quantidade' => $quantidade,
                    'volumes' => $volumes,
                    'prateleira' => $prateleira,
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }

    /**
     * Gera uma coleção com todos os atributos das caixas clonados da caixa
     * template e como filhas da prateleira informada.
     *
     * O número da primeira caixa será o definido no template enquanto as
     * demais serão incrementadas em um.
     *
     * @param \App\Models\Caixa      $template   template para criação das
     *                                           caixas
     * @param int                    $quantidade número de caixas para criar
     * @param \App\Models\Prateleira $prateleira prateleira pai
     *
     * @return \Illuminate\Support\Collection
     */
    private static function gerarMuitas(Caixa $template, int $quantidade, Prateleira $prateleira)
    {
        return
        collect()
        ->range($template->numero, $template->numero + $quantidade - 1)
        ->map(function ($sequencial) use ($template, $prateleira) {
            return [
                'ano' => $template->ano,
                'numero' => $sequencial,
                'descricao' => $template->descricao,
                'prateleira_id' => $prateleira->id,
            ];
        });
    }

    /**
     * Id de todas as caixas criadas.
     *
     * A busca é feita com base nas caixas antes de serem efetivamente
     * cadastrasdas.
     *
     * @param \Illuminate\Support\Collection $caixas antes da persistência.
     *
     * @return \Illuminate\Support\Collection
     */
    private static function ultimosIdsCadastrados(Collection $caixas)
    {
        return self::select('id')
        ->where('ano', $caixas->first()['ano'])
        ->whereBetween('numero', [$caixas->first()['numero'], $caixas->last()['numero']])
        ->get();
    }

    /**
     * Gera uma certa quantidade de volumes de caixa como filhas das caixas
     * informadas.
     *
     * O número de volumes em cada caixa é o mesmo e, em cada caixa, o número
     * de identificação do volume é incrementado de 1 em 1.
     *
     * @param int                            $quantidade número de volumes
     * @param \Illuminate\Support\Collection $caixas
     *
     * @return \Illuminate\Support\Collection
     */
    private static function gerarVolumes(int $quantidade, Collection $caixas)
    {
        return
        $caixas
        ->pluck('id')
        ->map(function ($caixa_id) use ($quantidade) {
            return collect()
            ->range(1, $quantidade)
            ->map(function ($sequencial) use ($caixa_id) {
                return [
                    'numero' => $sequencial,
                    'apelido' => "Vol. {$sequencial}",
                    'caixa_id' => $caixa_id,
                ];
            });
        })->flatten(1);
    }
}
