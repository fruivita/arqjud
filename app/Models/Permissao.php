<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Permissao extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'permissoes';

    // slug de todas as permissões da aplicação
    const CONFIGURACAO_VIEW = 'configuracao_view';

    const CONFIGURACAO_UPDATE = 'configuracao_update';

    const DELEGACAO_VIEW_ANY = 'delegacao_view_any';

    const DELEGACAO_CREATE = 'delegacao_create';

    const IMPORTACAO_CREATE = 'importacao_create';

    const LOG_VIEW_ANY = 'log_view_any';

    const LOG_VIEW = 'log_view';

    const LOG_DELETE = 'log_delete';

    const ATIVIDADE_VIEW_ANY = 'atividade_view_any';

    const ATIVIDADE_VIEW = 'atividade_view';

    const ATIVIDADE_DELETE = 'atividade_delete';

    const PERMISSAO_VIEW_ANY = 'permissao_view_any';

    const PERMISSAO_VIEW = 'permissao_view';

    const PERMISSAO_UPDATE = 'permissao_update';

    const PERFIL_VIEW_ANY = 'perfil_view_any';

    const PERFIL_VIEW = 'perfil_view';

    const PERFIL_CREATE = 'perfil_create';

    const PERFIL_UPDATE = 'perfil_update';

    const PERFIL_DELETE = 'perfil_delete';

    const USUARIO_VIEW_ANY = 'usuario_view_any';

    const USUARIO_VIEW = 'usuario_view';

    const USUARIO_UPDATE = 'usuario_update';

    const LOCALIDADE_VIEW_ANY = 'localidade_view_any';

    const LOCALIDADE_VIEW = 'localidade_view';

    const LOCALIDADE_CREATE = 'localidade_create';

    const LOCALIDADE_UPDATE = 'localidade_update';

    const LOCALIDADE_DELETE = 'localidade_delete';

    const PREDIO_VIEW_ANY = 'predio_view_any';

    const PREDIO_VIEW = 'predio_view';

    const PREDIO_CREATE = 'predio_create';

    const PREDIO_UPDATE = 'predio_update';

    const PREDIO_DELETE = 'predio_delete';

    const ANDAR_VIEW_ANY = 'andar_view_any';

    const ANDAR_VIEW = 'andar_view';

    const ANDAR_CREATE = 'andar_create';

    const ANDAR_UPDATE = 'andar_update';

    const ANDAR_DELETE = 'andar_delete';

    const SALA_VIEW_ANY = 'sala_view_any';

    const SALA_VIEW = 'sala_view';

    const SALA_CREATE = 'sala_create';

    const SALA_UPDATE = 'sala_update';

    const SALA_DELETE = 'sala_delete';

    const ESTANTE_VIEW_ANY = 'estante_view_any';

    const ESTANTE_VIEW = 'estante_view';

    const ESTANTE_CREATE = 'estante_create';

    const ESTANTE_UPDATE = 'estante_update';

    const ESTANTE_DELETE = 'estante_delete';

    const PRATELEIRA_VIEW_ANY = 'prateleira_view_any';

    const PRATELEIRA_VIEW = 'prateleira_view';

    const PRATELEIRA_CREATE = 'prateleira_create';

    const PRATELEIRA_UPDATE = 'prateleira_update';

    const PRATELEIRA_DELETE = 'prateleira_delete';

    const CAIXA_VIEW_ANY = 'caixa_view_any';

    const CAIXA_VIEW = 'caixa_view';

    const CAIXA_CREATE = 'caixa_create';

    const CAIXA_UPDATE = 'caixa_update';

    const CAIXA_DELETE = 'caixa_delete';

    const VOLUME_CAIXA_VIEW_ANY = 'volume_caixa_view_any';

    const VOLUME_CAIXA_VIEW = 'volume_caixa_view';

    const VOLUME_CAIXA_CREATE = 'volume_caixa_create';

    const VOLUME_CAIXA_UPDATE = 'volume_caixa_update';

    const VOLUME_CAIXA_DELETE = 'volume_caixa_delete';

    const PROCESSO_VIEW_ANY = 'processo_view_any';

    const PROCESSO_VIEW = 'processo_view';

    const PROCESSO_CREATE = 'processo_create';

    const PROCESSO_UPDATE = 'processo_update';

    const PROCESSO_DELETE = 'processo_delete';

    const MOVER_PROCESSO_CREATE = 'mover_processo_create';

    const SOLICITACAO_VIEW_ANY = 'solicitacao_view_any';

    const SOLICITACAO_CREATE = 'solicitacao_create';

    const SOLICITACAO_UPDATE = 'solicitacao_update';

    const SOLICITACAO_DELETE = 'solicitacao_delete';

    const SOLICITACAO_EXTERNA_VIEW_ANY = 'solicitacao_externa_view_any';

    const SOLICITACAO_EXTERNA_CREATE = 'solicitacao_externa_create';

    const SOLICITACAO_EXTERNA_DELETE = 'solicitacao_externa_delete';

    const GUIA_VIEW_ANY = 'guia_view_any';

    const GUIA_VIEW = 'guia_view';

    /**
     * Relacionamento permissão (M:N) perfis.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function perfis()
    {
        return $this->belongsToMany(Perfil::class, 'perfil_permissao', 'permissao_id', 'perfil_id')->withTimestamps();
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

        $query->where(function (Builder $query) use ($termo) {
            $query->where('nome', 'like', $termo)
                ->orWhere('slug', 'like', $termo);
        });
    }
}
