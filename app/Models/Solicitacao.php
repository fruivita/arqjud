<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 * @see https://laravel.com/docs/9.x/eloquent-mutators
 */
class Solicitacao extends Model
{
    use HasFactory;

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
     * destinatária registrada na solicitação.
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
     * Relacionamento solicitação (N:1) lotação(destinatária).
     *
     * Lotação onde o processo está alocado e, portanto, responsável pela sua
     * guarda.
     *
     * Negócio: essa é a lotação do usuário solicitante na data da solicitação.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lotacaoDestinataria()
    {
        return $this->belongsTo(Lotacao::class, 'lotacao_destinataria_id', 'id');
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
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSolicitadas($query)
    {
        return $query->whereNull(['entregue_em', 'devolvida_em']);
    }

    /**
     * Solicitações já entregues ao solicitante.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEntregues($query)
    {
        return $query
            ->whereNotNull('entregue_em')
            ->whereNull('devolvida_em');
    }

    /**
     * Solicitações devolvidas pelo solicitante ao arquivo.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDevolvidas($query)
    {
        return $query->whereNotNull(['entregue_em', 'devolvida_em']);
    }

    /**
     * Solicitações ativas, isto é, solicitações solicitadas ou entregues mas
     * ainda não devolvidas ao arquivo.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAtivas($query)
    {
        return $query->whereNull('devolvida_em');
    }

    /**
     * Status da solicitação.
     *
     * @return  \Illuminate\Database\Eloquent\Casts\Attribute
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
}
