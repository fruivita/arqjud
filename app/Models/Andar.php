<?php

namespace App\Models;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Andar extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'andares';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero'];

    /**
     * Relacionamento andar (N:1) prédio.
     *
     * Prédio do andar.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function predio()
    {
        return $this->belongsTo(Predio::class, 'predio_id', 'id');
    }

    /**
     * Relacionamento andar (1:N) salas.
     *
     * Salas do andar.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function salas()
    {
        return $this->hasMany(Sala::class, 'andar_id', 'id');
    }
}
