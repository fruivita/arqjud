<?php

namespace App\Models;

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
}
