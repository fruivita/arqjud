<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Prateleira extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'prateleiras';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero'];

    /**
     * Relacionamento prateleira (N:1) estante.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estante()
    {
        return $this->belongsTo(Estante::class, 'estante_id', 'id');
    }

    /**
     * Relacionamento prateleira (1:N) caixas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function caixas()
    {
        return $this->hasMany(Caixa::class, 'prateleira_id', 'id');
    }
}
