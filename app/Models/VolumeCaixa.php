<?php

namespace App\Models;

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
}
