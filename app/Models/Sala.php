<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Sala extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'salas';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero'];

    /**
     * Relacionamento sala (N:1) andar.
     *
     * Andar da sala.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function andar()
    {
        return $this->belongsTo(Andar::class, 'andar_id', 'id');
    }

    /**
     * Relacionamento sala (1:N) estantes.
     *
     * Estantes da sala.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function estantes()
    {
        return $this->hasMany(Estante::class, 'sala_id', 'id');
    }
}
