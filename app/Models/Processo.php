<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 * @see https://laravel.com/docs/9.x/eloquent-mutators
 */
class Processo extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'processos';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero', 'qtd_volumes', 'arquivado_em', 'guarda_permanente', 'numero_antigo'];

    /**
     * Máscara padrão para o número do processo do CNJ.
     *
     * @var string
     */
    public const MASCARA_CNJ = '#######-##.####.#.##.####';

    /**
     * Máscara padrão para o número do processo de 15 dígitos.
     *
     * @var string
     */
    public const MASCARA_V2 = '####.##.##.######-#';

    /**
     * Relacionamento processo (N:1) volume da caixa.
     *
     * Volume da caixa em que o processo está armazenado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function volumeCaixa()
    {
        return $this->belongsTo(VolumeCaixa::class, 'volume_caixa_id', 'id');
    }

    /**
     * Relacionamento processo filho (N:1) processo pai.
     *
     * Processo pai de um determinado processo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function processoPai()
    {
        return $this->belongsTo(Processo::class, 'processo_pai_id', 'id');
    }

    /**
     * Relacionamento processo pai (1:N) Processos filho.
     *
     * Processos filho de um determinada processo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function processosFilho()
    {
        return $this->hasMany(Processo::class, 'processo_pai_id', 'id');
    }

    /**
     * Relacionamento processo (1:N) solicitações.
     *
     * Solicitações feitas com o processo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitacoes()
    {
        return $this->hasMany(Solicitacao::class, 'processo_id', 'id');
    }
}
