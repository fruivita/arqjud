<?php

namespace App\Models;

use App\Models\Traits\ComHumanizacao;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class VolumeCaixa extends Model
{
    use ComHumanizacao;
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'volumes_caixa';

    /**
     * Relacionamento volumes da caixa (N:1) caixa.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id', 'id');
    }

    /**
     * Todos os volumes das caixas.
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
     * - prateleira_id: id da prateleira pai
     * - prateleira_apelido: apelido da prateleira pai
     * - prateleira_numero: número da prateleira pai
     * - caixa_numero: número da caixa pai
     * - caixa_ano: ano da caixa pai
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function hierarquia()
    {
        return
        self::join('caixas', 'volumes_caixa.caixa_id', '=', 'caixas.id')
        ->join('prateleiras', 'caixas.prateleira_id', '=', 'prateleiras.id')
        ->join('estantes', 'prateleiras.estante_id', '=', 'estantes.id')
        ->join('salas', 'estantes.sala_id', '=', 'salas.id')
        ->join('andares', 'salas.andar_id', '=', 'andares.id')
        ->join('predios', 'andares.predio_id', '=', 'predios.id')
        ->join('localidades', 'predios.localidade_id', '=', 'localidades.id')
        ->select([
            'volumes_caixa.*',
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
            'prateleiras.id as prateleira_id',
            'prateleiras.apelido as prateleira_apelido',
            'prateleiras.numero as prateleira_numero',
            'caixas.numero as caixa_numero',
            'caixas.ano as caixa_ano',
        ])
        ->groupBy('volumes_caixa.id');
    }

    /**
     * Volume da caixa em formato para leitura humana.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function paraHumano(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->humanizarVolumeCaixa($this->numero)
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
     * Caixa em formato para leitura humana.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function caixaParaHumano(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->humanizarCaixa($this->caixa_numero, $this->caixa_ano)
        );
    }

    /**
     * Gera uma certa quantidade de volumes incrementados de 1 em 1 a partir do
     * número informado. Os objetos não estão persistidos.
     *
     * Notar que o número informado será o número do primeiro volume.
     *
     * @param int $quantidade número de volumes
     * @param int $primeiro   número do primeiro volume
     *
     * @return \Illuminate\Support\Collection
     */
    public static function gerar(int $quantidade, int $primeiro = 1)
    {
        return collect()
        ->range($primeiro, $primeiro + $quantidade - 1)
        ->map(function ($sequencial) {
            $volume = new self();

            $volume->numero = $sequencial;
            $volume->apelido = $volume->humanizarVolumeCaixa($sequencial);

            return $volume;
        });
    }

    /**
     * Volumes da caixa informada.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id_caixa
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDaCaixa($query, int $id_caixa)
    {
        return $query->where('caixa_id', $id_caixa);
    }
}
