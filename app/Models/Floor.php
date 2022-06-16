<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Floor extends Model
{
    use HasFactory;

    protected $table = 'floors';

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
     * All floors.
     *
     * Extra columns:
     * - site_name: parent site name
     * - building_name: parent building name
     * - rooms_count: child rooms count
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public static function hierarchy()
    {
        return
        self::join('buildings', 'floors.building_id', '=', 'buildings.id')
        ->join('sites', 'buildings.site_id', '=', 'sites.id')
        ->leftJoin('rooms', 'rooms.floor_id', '=', 'floors.id')
        ->select([
            'floors.*',
            'buildings.name as building_name',
            'sites.name as site_name',
            DB::raw('COUNT(rooms.floor_id) as rooms_count')
        ])
        ->groupBy('floors.id');
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
            __('Site') => route('archiving.register.site.show', $this->building->site),
            __('Building') => route('archiving.register.building.show', $this->building),
        ])->when($root, function ($collection) {
            return $collection->put(__('Floor'), route('archiving.register.floor.show', $this));
        });
    }

    /**
     * Save the informed room as a child of the floor. Also create the default
     * stand and shelf.
     *
     * The default stand and shelf are the ones that has not been
     * reviewed/created by user request.
     *
     * @param \App\Models\Room $room
     *
     * @return bool
     */
    public function createRoom(Room $room)
    {
        try {
            DB::transaction(function () use ($room) {
                $this->rooms()->save($room);

                $stand = Stand::uninformedStand();

                $room->stands()->save($stand);

                $stand->shelves()->save(Shelf::uninformedShelf());
            });

            return true;
        } catch (\Throwable $th) {
            Log::error(
                __('Shelf creation failed'),
                [
                    'floor' => $this,
                    'room' => $room,
                    'exception' => $th,
                ]
            );

            return false;
        }
    }
}
