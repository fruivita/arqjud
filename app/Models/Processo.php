<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * Pressupõe join com as tabelas:
     * - Localidades;
     * - Prédios;
     * - Andares;
     * - Salas;
     * - Estantes;
     * - Prateleiras;
     * - Criadoras (Localidades criadoras das caixas);
     * - Volumes_caixa
     *
     * Colunas pesquisadas:
     * - nome da localidade;
     * - nome do prédio;
     * - número do andar;
     * - apelido do andar;
     * - número da sala;
     * - número da estante;
     * - número da prateleira;
     * - nome da localidade criadora da caixa;
     * - número da caixa;
     * - ano da caixa;
     * - complemento da caixa;
     * - número do volume da caixa;
     * - número do processo;
     * - número antigo do processo;
     * - quantidade de volumes do processo;
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $termo
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $termo = "{$termo}%";

        $query->where('localidades.nome', 'like', $termo)
            ->orWhere('predios.nome', 'like', $termo)
            ->orWhere('andares.numero', 'like', $termo)
            ->orWhere('andares.apelido', 'like', $termo)
            ->orWhere('salas.numero', 'like', $termo)
            ->orWhere('estantes.numero', 'like', $termo)
            ->orWhere('prateleiras.numero', 'like', $termo)
            ->orWhere('criadoras.nome', 'like', $termo)
            ->orWhere('caixas.numero', 'like', $termo)
            ->orWhere('caixas.ano', 'like', $termo)
            ->orWhere('caixas.complemento', 'like', $termo)
            ->orWhere('volumes_caixa.numero', 'like', $termo)
            ->orWhere('processos.numero', 'like', $termo)
            ->orWhere('processos.numero_antigo', 'like', $termo)
            ->orWhere('processos.qtd_volumes', 'like', $termo);
    }
}
