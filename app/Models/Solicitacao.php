<?php

namespace App\Models;

use App\Models\Trait\Auditavel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 * @see https://laravel.com/docs/9.x/eloquent-mutators
 */
class Solicitacao extends Model
{
    use HasFactory, Auditavel;

    /**
     * {@inheritdoc}
     */
    protected $table = 'solicitacoes';

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'solicitada_em' => 'datetime',
        'entregue_em' => 'datetime',
        'devolvida_em' => 'datetime',
        'por_guia' => 'boolean',
    ];

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['processo_id', 'solicitante_id', 'destino_id', 'solicitada_em', 'por_guia'];

    /**
     * Relacionamento solicitação (N:1) processo.
     *
     * Processso que a solicitação se refere.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function processo()
    {
        return $this->belongsTo(Processo::class, 'processo_id', 'id');
    }

    /**
     * Relacionamento solicitação (N:1) usuário(solicitante).
     *
     * Usuário que solicitou o processo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function solicitante()
    {
        return $this->belongsTo(Usuario::class, 'solicitante_id', 'id');
    }

    /**
     * Relacionamento solicitação (N:1) usuário(recebedor).
     *
     * Usuário a quem efetivamente foi entregue o processo.
     *
     * Negócio: esse usuário deve obrigatoriamente pertencer a lotação
     * (destino) registrada na solicitação.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recebedor()
    {
        return $this->belongsTo(Usuario::class, 'recebedor_id', 'id');
    }

    /**
     * Relacionamento solicitação (N:1) usuário(remetente).
     *
     * Usuário que efetivamente fez a estrega do processo ao portador.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function remetente()
    {
        return $this->belongsTo(Usuario::class, 'remetente_id', 'id');
    }

    /**
     * Relacionamento solicitação (N:1) usuário(rearquivador).
     *
     * Usuário a quem a solicitação foi devolvida e resposável por rearquivar o
     * processo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rearquivador()
    {
        return $this->belongsTo(Usuario::class, 'rearquivador_id', 'id');
    }

    /**
     * Relacionamento solicitação (N:1) lotação(destino).
     *
     * Lotação onde o processo está alocado e, portanto, responsável pela sua
     * guarda.
     *
     * Negócio: essa é a lotação do usuário solicitante na data da solicitação.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function destino()
    {
        return $this->belongsTo(Lotacao::class, 'destino_id', 'id');
    }

    /**
     * Relacionamento solicitações (N:1) guia(de remessa).
     *
     * Guia em que a solicitação foi registrada.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function guia()
    {
        return $this->belongsTo(Guia::class, 'guia_id', 'id');
    }

    /**
     * Solicitações solicitadas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeSolicitadas($query)
    {
        $query->whereNull(['entregue_em', 'devolvida_em']);
    }

    /**
     * Solicitações já entregues ao solicitante.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeEntregues($query)
    {
        $query
            ->whereNotNull('entregue_em')
            ->whereNull('devolvida_em');
    }

    /**
     * Solicitações devolvidas pelo solicitante ao arquivo.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeDevolvidas($query)
    {
        $query->whereNotNull(['entregue_em', 'devolvida_em']);
    }

    /**
     * Solicitações ativas, isto é, solicitações solicitadas ou entregues mas
     * ainda não devolvidas ao arquivo.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeAtivas($query)
    {
        $query->whereNull('devolvida_em');
    }

    /**
     * Status da solicitação.
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (empty($attributes['entregue_em'])) {
                    return __('solicitada');
                } elseif (empty($attributes['devolvida_em'])) {
                    return __('entregue');
                } else {
                    return __('devolvida');
                }
            }
        );
    }

    /**
     * Permite a contagem dos tipos de solicitações em uma única query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeCountAll($query)
    {
        $query->selectRaw('COUNT(CASE WHEN entregue_em is null THEN 1 END) as solicitadas')
            ->selectRaw('COUNT(CASE WHEN entregue_em is not null AND devolvida_em is null THEN 1 END) as entregues')
            ->selectRaw('COUNT(CASE WHEN devolvida_em is not null THEN 1 END) as devolvidas');
    }

    /**
     * Ordena as solicitações com base em seu status.
     *
     * 1º Solicitadas;
     * 2º Entregues;
     * 3º Devolvidas.
     *
     * Em todos os casos,
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     *
     * @see https://learnsql.com/blog/how-to-order-rows-with-nulls/
     */
    public function scopeOrderByStatus($query)
    {
        $query
            ->orderByRaw('devolvida_em IS NOT NULL')
            ->orderBy('devolvida_em', 'desc')
            ->orderByRaw('entregue_em IS NOT NULL')
            ->orderBy('entregue_em', 'desc')
            ->orderBy('solicitada_em', 'desc');
    }

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $termo = "{$termo}%";
        $apenas_numeros = apenasNumeros($termo);

        $query->where(function (Builder $query) use ($termo, $apenas_numeros) {
            $query->where('solicitantes.matricula', 'like', $termo)
                ->orWhere('solicitantes.nome', 'like', $termo)
                ->orWhere('recebedores.matricula', 'like', $termo)
                ->orWhere('recebedores.nome', 'like', $termo)
                ->orWhere('remetentes.matricula', 'like', $termo)
                ->orWhere('remetentes.nome', 'like', $termo)
                ->orWhere('rearquivadores.matricula', 'like', $termo)
                ->orWhere('rearquivadores.nome', 'like', $termo)
                ->orWhere('destinos.sigla', 'like', $termo)
                ->orWhere('destinos.nome', 'like', $termo)
                ->when($apenas_numeros, function (Builder $query, string $apenas_numeros) {
                    $query->orWhere('processos.numero', 'like', "{$apenas_numeros}%")
                        ->orWhere('processos.numero_antigo', 'like', "{$apenas_numeros}%");
                });
        });
    }
}
