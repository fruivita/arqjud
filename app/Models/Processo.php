<?php

namespace App\Models;

use App\Casts\NumeroProcesso;
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
     * {@inheritdoc}
     */
    protected $casts = [
        'numero' => NumeroProcesso::class,
        'numero_antigo' => NumeroProcesso::class,
        'arquivado_em' => 'date',
        'guarda_permanente' => 'boolean',
    ];

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
     * Máscara padrão para o número do processo de 10 dígitos.
     *
     * @var string
     */
    public const MASCARA_V1 = '##.#######-#';

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
     * Relacionamento processo (1:N) solicitações solicitadas.
     *
     * Solicitações solicitadas do processo.
     *
     * Nota: no cenário ideal, só haverá uma solicitação ativa. Contudo, visto
     * que se trata de uma especialização de um relacionamento 1:N, foi mantido
     * como um relacionamento 1:N.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitacoesSolicitadas()
    {
        return $this->solicitacoes()->solicitadas();
    }

    /**
     * Relacionamento processo (1:N) solicitações entregues.
     *
     * Solicitações entregues do processo.
     *
     * Nota: no cenário ideal, só haverá uma entrega ativa. Contudo, visto que
     * se trata de uma especialização de um relacionamento 1:N, foi mantido
     * como um relacionamento 1:N.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitacoesEntregues()
    {
        return $this->solicitacoes()->entregues();
    }

    /**
     * Relacionamento processo (1:N) solicitações devolvidas.
     *
     * Solicitações devolvidas do processo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitacoesDevolvidas()
    {
        return $this->solicitacoes()->devolvidas();
    }

    /**
     * Relacionamento processo (1:N) solicitações ativas.
     *
     * Solicitações ativas são as solicitações solicitadas ou entregues, ou
     * seja, solicitações não devolvidas.
     *
     * Nota: no cenário ideal, só haverá uma solicitações ativa. Contudo, visto
     * que se trata de uma especialização de um relacionamento 1:N, foi mantido
     * como um relacionamento 1:N.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solicitacoesAtivas()
    {
        return $this->solicitacoes()->ativas();
    }

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $termo
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $termo = "{$termo}%";
        $apenas_numeros = apenasNumeros($termo);

        $query->where(function (Builder $query) use ($termo, $apenas_numeros) {
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
                ->when($apenas_numeros, function (Builder $query, string $apenas_numeros) {
                    $query->orWhere('processos.numero', 'like', "{$apenas_numeros}%")
                        ->orWhere('processos.numero_antigo', 'like', "{$apenas_numeros}%");
                })
                ->orWhere('processos.qtd_volumes', 'like', $termo);
        });
    }

    /**
     * Aplica ao número de informado à mascara a mascara CNJ, V2 ou V1
     * necessária.
     *
     * @param  null|string  $processo
     * @return null|string processo com máscara ou null
     */
    public static function aplicarMascaraProcesso(string $processo = null)
    {
        $processo = trim($processo);

        switch (str($processo)->length()) {
            case 20:
                $processo = cnj($processo);
                break;

            case 15:
                $processo = v2($processo);
                break;

            case 10:
                $processo = v1($processo);
                break;
        }

        return $processo ?: null;
    }
}
