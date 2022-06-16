<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

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
     * Relationship room (1:N) stands.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stands()
    {
        return $this->hasMany(Stand::class, 'room_id', 'id');
    }

    /**
     * All rooms.
     *
     * Extra columns:
     * - site_name: parent site name
     * - building_name: parent building name
     * - floor_number: parent floor number
     * - stands_count: child stands count
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public static function hierarchy()
    {
        return
        self::join('floors', 'rooms.floor_id', '=', 'floors.id')
        ->join('buildings', 'floors.building_id', '=', 'buildings.id')
        ->join('sites', 'buildings.site_id', '=', 'sites.id')
        ->leftJoin('stands', 'stands.room_id', '=', 'rooms.id')
        ->select([
            'rooms.*',
            'floors.number as floor_number',
            'buildings.name as building_name',
            'sites.name as site_name',
            DB::raw('COUNT(stands.room_id) as stands_count')
        ])
        ->groupBy('rooms.id');
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
            __('Site') => route('archiving.register.site.show', $this->floor->building->site),
            __('Building') => route('archiving.register.building.show', $this->floor->building),
            __('Floor') => route('archiving.register.floor.show', $this->floor),
        ])->when($root, function ($collection) {
            return $collection->put(__('Room'), route('archiving.register.room.show', $this));
        });
    }

    /**
     * Save the informed stand as a child of the room. Also create the default
     * shelf.
     *
     * The default shelf is the one that has not been reviewed/created by user
     * request.
     *
     * @param \App\Models\Stand $stand
     *
     * @return bool
     */
    public function createStand(Stand $stand)
    {
        try {
            DB::transaction(function () use ($stand) {
                $this->stands()->save($stand);

                $stand->shelves()->save(Shelf::uninformedShelf());
            });

            return true;
        } catch (\Throwable $th) {
            Log::error(
                __('Stand creation failed'),
                [
                    'room' => $this,
                    'stand' => $stand,
                    'exception' => $th,
                ]
            );

            return false;
        }
    }
}
