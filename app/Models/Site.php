<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Site extends Model
{
    use HasEagerLimit;
    use HasFactory;

    protected $table = 'sites';

    /**
     * Default ordering of the model.
     *
     * Order: name asc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultOrder($query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * Previous record.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function previous()
    {
        return Site::select('id')
        ->whereRaw('name < (select name from sites where id = ?)', [$this->id])
        ->orderBy('name', 'desc')
        ->take(1);
    }

    /**
     * Next record.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function next()
    {
        return Site::select('id')
        ->whereRaw('name > (select name from sites where id = ?)', [$this->id])
        ->orderBy('name', 'asc')
        ->take(1);
    }
}
