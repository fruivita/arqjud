<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Perfil extends Model
{
    use HasEagerLimit;
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'perfis';

    /**
     * Indica se os IDs são auto incrementáveis.
     *
     * @var bool
     */
    public $incrementing = false;

    // Ids dos perfis cadastrados no banco
    public const ADMINISTRADOR = 9000;
    public const GERENTE_NEGOCIO = 8000;
    public const OBSERVADOR = 7000;
    public const PADRAO = 1000;

    /**
     * Relacionamento perfil (N:M) permissões.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissoes()
    {
        return $this->belongsToMany(Permissao::class, 'perfil_permissao', 'perfil_id', 'permissao_id')->withTimestamps();
    }

    /**
     * Relacionamento perfil (1:N) usuários.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'perfil_id', 'id');
    }

    /**
     * Ordenação padrão do modelo.
     *
     * Ordenação: Id desc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdenacaoPadrao($query)
    {
        return $query->orderBy('id', 'desc');
    }

    /**
     * Perfis disponíveis para atribuir a outro usuário.
     *
     * Os perfis disponíveis dependem do perfil do usuário autenticado, visto
     * que ele só pode atribuir a outro, perfis de igual ou menor autorizações.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisponiveisParaAtribuicao($query)
    {
        return $query->where('id', '<=', auth()->user()->perfil_id);
    }

    /**
     * Salva o perfil no banco de dados e sincroniza as permissões informadas
     * em uma operação atômica, isto é, tudo ou nada.
     *
     * @param array|int|null $permissoes ids das permissoes
     *
     * @return bool
     */
    public function salvaESincronizaPermissoes(mixed $permissoes)
    {
        try {
            DB::transaction(function () use ($permissoes) {
                $this->save();

                $this->permissoes()->sync($permissoes);
            });

            return true;
        } catch (\Throwable $exception) {
            Log::error(
                __('Falha na atualização do perfil'),
                [
                    'perfil' => $this,
                    'permissoes' => $permissoes,
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }
}
