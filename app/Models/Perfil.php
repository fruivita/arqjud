<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Perfil extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'perfis';

    // Slug dos perfis da aplicação
    const ADMINISTRADOR = 'administrador';

    const GERENTE_NEGOCIO = 'gerente-de-negocio';

    const OPERADOR = 'operador';

    const OBSERVADOR = 'observador';

    const PADRAO = 'padrao';

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
     * Perfis disponíveis para atribuir a outro usuário.
     *
     * Os perfis disponíveis dependem do perfil do usuário autenticado, visto
     * que ele só pode atribuir a outro, perfis de igual ou menor autorizações.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return void
     */
    public function scopeDisponiveisParaAtribuicao(Builder $query)
    {
        auth()->user()->loadMissing('perfil');

        $query->where('poder', '<=', auth()->user()->perfil->poder);
    }
}
