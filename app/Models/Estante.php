<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Estante extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'estantes';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['numero'];

    /**
     * Relacionamento estante (N:1) sala.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sala()
    {
        return $this->belongsTo(Sala::class, 'sala_id', 'id');
    }

    /**
     * Relacionamento estante (1:N) prateleiras.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prateleiras()
    {
        return $this->hasMany(Prateleira::class, 'estante_id', 'id');
    }
}
