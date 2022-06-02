<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Site extends Model
{
    use HasFactory;

    protected $table = 'sites';

    /**
     * The relationship counts that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withCount = ['buildings'];

    /**
     * Relationship site (1:N) buildings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function buildings()
    {
        return $this->hasMany(Building::class, 'site_id', 'id');
    }

    /**
     * Default ordering of the model.
     *
     * Order: name asc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultOrder(Builder $query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * Previous record based on defaultOrder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function previous()
    {
        return self::select('id')
        ->whereRaw('name < (select name from sites where id = ?)', [$this->id])
        ->orderBy('name', 'desc')
        ->take(1);
    }

    /**
     * Next record based on defaultOrder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function next()
    {
        return self::select('id')
        ->whereRaw('name > (select name from sites where id = ?)', [$this->id])
        ->orderBy('name', 'asc')
        ->take(1);
    }
}
