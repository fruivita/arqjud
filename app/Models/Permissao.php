<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Permissao extends Model
{
    use HasEagerLimit;
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'permissoes';

    /**
     * Indica se os IDs são auto incrementáveis.
     *
     * @var bool
     */
    public $incrementing = false;

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
     * Ordenação padrão do modelo.
     *
     * Ordenação: Id asc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdenacaoPadrao(Builder $query)
    {
        return $query->orderBy('id', 'asc');
    }

    /**
     * Salva a permissão no banco de dados e sincroniza os perfis informados em
     * uma operação atômica, isto é, tudo ou nada.
     *
     * @param array|int|null $perfis ids dos perfis
     *
     * @return bool
     */
    public function salvaESincronizaPerfis(mixed $perfis)
    {
        try {
            DB::transaction(function () use ($perfis) {
                $this->save();

                $this->perfis()->sync($perfis);
            });

            return true;
        } catch (\Throwable $exception) {
            Log::error(
                __('Falha na atualização da permissão'),
                [
                    'permissao' => $this,
                    'perfis' => $perfis,
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }
}
