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
     * Links to the parent entities.
     *
     * @return \Illuminate\Support\Collection
     */
    public function parentEntitiesLinks()
    {
        return collect([
            __('Site') => route('archiving.register.site.show', $this->floor->building->site),
            __('Building') => route('archiving.register.building.show', $this->floor->building),
            __('Floor') => route('archiving.register.floor.show', $this->floor),
        ]);
    }
}
