<?php

namespace App\Models;

use App\Models\Traits\ComHumanizacao;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Caixa extends Model
{
    use ComHumanizacao;
    use HasFactory;

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
     * Os atributos sujeitos ao cast.
     *
     * @var array<string, string>
     */
    protected $casts = ['guarda_permanente' => 'boolean'];

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
     * Relacionamento caixa (N:1) localidade.
     *
     * Localidade em que a caixa foi criada.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function localidadeCriadora()
    {
        return $this->belongsTo(Localidade::class, 'localidade_criadora_id', 'id');
    }

    /**
     * Todas as caixas.
     *
     * Acompanhadas das seguintes colunas extras:
     * - localidade_id: id da localidade pai
     * - localidade_nome: nome da localidade pai
     * - localidade_criadora_nome: nome da localidade criadora da caixa
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
        ->join('localidades AS criadoras', 'caixas.localidade_criadora_id', '=', 'criadoras.id')
        ->leftJoin('volumes_caixa', 'volumes_caixa.caixa_id', '=', 'caixas.id')
        ->select([
            'caixas.*',
            'localidades.id as localidade_id',
            'localidades.nome as localidade_nome',
            'criadoras.nome as localidade_criadora_nome',
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
            DB::raw('COUNT(volumes_caixa.caixa_id) as volumes_count'),
        ])
        ->groupBy('caixas.id');
    }

    /**
     * Campos hierarquizados do modelo.
     *
     * Chaves:
     * - localidade_id: id da localidade pai
     * - localidade_nome: nome da localidade pai
     * - localidade_criadora_id: id da localidade criadora da caixa
     * - localidade_criadora_nome: nome da localidade criadora da caixa
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
            'localidade_criadora_id' => $caixa->localidade_criadora_id,
            'localidade_criadora_nome' => $caixa->localidade_criadora_nome,
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
     * @param int    $ano                    ano para criação da caixa
     * @param bool   $guarda_permanente      se é para guarda permanente
     * @param int    $localidade_criadora_id onde a caixa foi criada
     * @param string $complemento            complementação
     *
     * @return int
     */
    public static function proximoNumeroCaixa(
        int $ano,
        bool $guarda_permanente,
        int $localidade_criadora_id,
        string $complemento = null
    ) {
        return self::where('ano', $ano)
                    ->where('guarda_permanente', $guarda_permanente)
                    ->where('localidade_criadora_id', $localidade_criadora_id)
                    ->when(
                        $complemento,
                        function ($query, $complemento) {
                            $query->where('complemento', $complemento);
                        },
                        function ($query) {
                            $query->whereNull('complemento');
                        }
                    )
                    ->max('numero') + 1;
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
     * @param \App\Models\Caixa      $template    template para a criação das
     *                                            caixas
     * @param int                    $quantidade  número de caixas para criação
     * @param int                    $qtd_volumes número de volumes das caixas
     * @param \App\Models\Prateleira $prateleira  prateleira pai
     *
     * @return bool
     */
    public static function criarMuitas(Caixa $template, int $quantidade, int $qtd_volumes, Prateleira $prateleira)
    {
        try {
            DB::transaction(function () use ($template, $quantidade, $qtd_volumes, $prateleira) {
                $caixas = self::gerar($template, $quantidade);

                $prateleira->caixas()->saveMany($caixas);

                $caixas->each(function ($caixa) use ($qtd_volumes) {
                    $volumes = VolumeCaixa::gerar($qtd_volumes);

                    $caixa->volumes()->saveMany($volumes);

                    unset($volumes);
                });
            });

            return true;
        } catch (\Throwable $exception) {
            Log::error(
                __('Falha na criação da caixa'),
                [
                    'template' => $template,
                    'quantidade' => $quantidade,
                    'qtd_volumes' => $qtd_volumes,
                    'prateleira' => $prateleira,
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }

    /**
     * Gera uma coleção com todos os atributos das caixas clonados da caixa
     * template. Os objetos não estão persistidos.
     *
     * O número da primeira caixa será o definido no template enquanto as
     * demais serão incrementadas em um.
     *
     * @param \App\Models\Caixa $template   template para criação das caixas
     * @param int               $quantidade número de caixas para criar
     *
     * @return \Illuminate\Support\Collection
     */
    private static function gerar(Caixa $template, int $quantidade)
    {
        return
        collect()
        ->range($template->numero, $template->numero + $quantidade - 1)
        ->map(function ($sequencial) use ($template) {
            $caixa = clone $template;
            $caixa->numero = $sequencial;

            return $caixa;
        });
    }

    /**
     * Cria o volume de número informado para a caixa.
     *
     * @param int $numero_volume
     *
     * @return false|\Illuminate\Database\Eloquent\Model
     */
    public function criarVolume(int $numero_volume)
    {
        /** @var \App\Models\VolumeCaixa */
        $volume = VolumeCaixa::gerar(1, $numero_volume)->first();

        return $this->volumes()->save($volume);
    }

    /**
     * Caixas da prateleira informada.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int                                   $id_prateleira
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDaPrateleira($query, int $id_prateleira)
    {
        return $query->where('prateleira_id', $id_prateleira);
    }
}
