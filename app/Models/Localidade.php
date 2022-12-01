<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Localidade extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $table = 'localidades';

    /**
     * {@inheritdoc}
     */
    protected $fillable = ['nome'];

    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * * Colunas pesquisadas:
     * - nome da localidade
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $query->where('nome', 'like', "{$termo}%");
    }
}
