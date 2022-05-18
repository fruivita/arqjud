<?php

namespace App\Models;

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
    public function scopeDefaultOrder($query)
    {
        return $query->orderBy('number', 'asc');
    }

    /**
     * Previous record.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function previous()
    {
        return BoxVolume::select('id')
        ->whereRaw('number < (select number from box_volumes where id = ?)', [$this->id])
        ->orderBy('number', 'desc')
        ->take(1);
    }

    /**
     * Next record.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function next()
    {
        return BoxVolume::select('id')
        ->whereRaw('number > (select number from box_volumes where id = ?)', [$this->id])
        ->orderBy('number', 'asc')
        ->take(1);
    }
}
