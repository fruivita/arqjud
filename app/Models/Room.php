<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    /**
     * The relationship counts that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withCount = ['boxes'];

    /**
     * Relationship room (N:1) floor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id', 'id');
    }

    /**
     * Relationship room (1:N) boxes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function boxes()
    {
        return $this->hasMany(Box::class, 'room_id', 'id');
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
    public function scopeDefaultOrder(Builder $query)
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
        ->whereRaw('number < (select number from rooms where id = ?)', [$this->id])
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
        ->whereRaw('number > (select number from rooms where id = ?)', [$this->id])
        ->orderBy('number', 'asc')
        ->take(1);
    }
}
