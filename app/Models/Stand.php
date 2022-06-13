<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Stand extends Model
{
    use HasFactory;

    protected $table = 'stands';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['number', 'description'];

    /**
     * Relationship stand (N:1) room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    /**
     * Relationship stand (1:N) Shelves.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shelves()
    {
        return $this->hasMany(Shelf::class, 'stand_id', 'id');
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
    public function parentLinks(bool $root)
    {
        return collect([
            __('Site') => route('archiving.register.site.show', $this->room->floor->building->site),
            __('Building') => route('archiving.register.building.show', $this->room->floor->building),
            __('Floor') => route('archiving.register.floor.show', $this->room->floor),
            __('Room') => route('archiving.register.room.show', $this->room),
        ])->when($root, function ($collection) {
            return $collection->put(__('Stand'), route('archiving.register.stand.show', $this));
        });
    }

    /**
     * Default stand to be used when creating a room.
     *
     * @return self
     */
    public static function uninformedStand()
    {
        return new self([
            'number' => 0,
            'description' => __('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored'),

        ]);
    }
}
