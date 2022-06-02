<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/eloquent
 */
class BoxVolume extends Model
{
    use HasFactory;

    protected $table = 'box_volumes';

    /**
     * Relationship box volumes (N:1) box.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id', 'id');
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
        ->whereRaw('number < (select number from box_volumes where id = ?)', [$this->id])
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
        ->whereRaw('number > (select number from box_volumes where id = ?)', [$this->id])
        ->orderBy('number', 'asc')
        ->take(1);
    }
}
