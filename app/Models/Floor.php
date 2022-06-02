<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Floor extends Model
{
    use HasFactory;

    protected $table = 'floors';

    /**
     * The relationship counts that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withCount = ['rooms'];

    /**
     * Relationship floor (N:1) building.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'id');
    }

    /**
     * Relationship floor (1:N) rooms.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rooms()
    {
        return $this->hasMany(Room::class, 'floor_id', 'id');
    }

    /**
     * Default ordering of the model.
     *
     * Order: number asc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultOrder($query)
    {
        return $query->orderBy('number', 'asc');
    }

    /**
     * Previous record based on defaultOrder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function previous()
    {
        return self::select('id')
        ->whereRaw('number < (select number from floors where id = ?)', [$this->id])
        ->orderBy('number', 'desc')
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
        ->whereRaw('number > (select number from floors where id = ?)', [$this->id])
        ->orderBy('number', 'asc')
        ->take(1);
    }
}
