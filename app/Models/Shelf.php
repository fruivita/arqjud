<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Shelf extends Model
{
    use HasFactory;

    protected $table = 'shelves';

    /**
     * Relationship shelf (N:1) stand.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stand()
    {
        return $this->belongsTo(Stand::class, 'stand_id', 'id');
    }

    /**
     * Relationship shelf (1:N) boxes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function boxes()
    {
        return $this->hasMany(Box::class, 'shelf_id', 'id');
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
     * @param bool $root must include the root element?
     *
     * @return \Illuminate\Support\Collection
     */
    public function parentEntitiesLinks(bool $root)
    {
        return collect([
            __('Site') => route('archiving.register.site.show', $this->stand->room->floor->building->site),
            __('Building') => route('archiving.register.building.show', $this->stand->room->floor->building),
            __('Floor') => route('archiving.register.floor.show', $this->stand->room->floor),
            __('Room') => route('archiving.register.room.show', $this->stand->room),
            __('Stand') => route('archiving.register.stand.show', $this->stand),
        ])->when($root, function ($collection) {
            return $collection->put(__('Shelf'), route('archiving.register.shelf.show', $this));
        });
    }
}
