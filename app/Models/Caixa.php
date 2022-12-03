<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
}
