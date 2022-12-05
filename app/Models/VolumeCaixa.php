<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class VolumeCaixa extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'volumes_caixa';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero'];

    /**
     * Relacionamento volumes da caixa (N:1) caixa.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id', 'id');
    }

    /**
     * Relacionamento volume da caixa (1:N) processos.
     *
     * Processos armazenados em um determinado volume da caixa.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function processos()
    {
        return $this->hasMany(Processo::class, 'volume_caixa_id', 'id');
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
     * - Criadoras (Localidades criadoras das caixas).
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
            ->orWhere('volumes_caixa.numero', 'like', $termo);
    }
}
