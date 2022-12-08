<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 * @see https://laravel.com/docs/9.x/eloquent-mutators
 */
class Caixa extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'caixas';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero', 'ano', 'guarda_permanente', 'complemento', 'descricao', 'localidade_criadora_id'];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'guarda_permanente' => 'boolean',
    ];

    /**
     * Relacionamento caixa (N:1) prateleira.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prateleira()
    {
        return $this->belongsTo(Prateleira::class, 'prateleira_id', 'id');
    }

    /**
     * Relacionamento caixa (1:N) volumes da caixa.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function volumes()
    {
        return $this->hasMany(VolumeCaixa::class, 'caixa_id', 'id');
    }

    /**
     * Relacionamento caixa (N:1) localidade.
     *
     * Localidade em que a caixa foi criada.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function localidadeCriadora()
    {
        return $this->belongsTo(Localidade::class, 'localidade_criadora_id', 'id');
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
            ->orWhere('caixas.complemento', 'like', $termo);
    }
}
