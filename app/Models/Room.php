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

    /**
     * The table associated with the model.
     *
     * @var string
     */
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
     * - site_id: parent site id
     * - site_name: parent site name
     * - building_id: parent building id
     * - building_name: parent building name
     * - floor_alias: parent floor alias
     * - floor_number: parent floor number
     * - stands_count: child stands count
     *
     * @return \Illuminate\Database\Query\Builder
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
            'sites.id as site_id',
            'sites.name as site_name',
            'buildings.id as building_id',
            'buildings.name as building_name',
            'floors.alias as floor_alias',
            'floors.number as floor_number',
            DB::raw('COUNT(stands.room_id) as stands_count')
        ])
        ->groupBy('rooms.id');
    }

    /**
     * Model hierarchical fields.
     *
     * keys:
     * - site_id: parent site id
     * - site_name: parent site name
     * - building_id: parent building id
     * - building_name: parent building name
     * - floor_id: parent floor id
     * - floor_number: parent floor number
     * - floor_alias: parent floor alias
     * - stands_count: child stands count
     *
     * @return \Illuminate\Support\Collection
     */
    private function hierarchicalData()
    {
        $room = isset($this->site_name)
        ? $this
        : self::hierarchy()->find($this->id);

        return collect([
            'site_id' => $room->site_id,
            'site_name' => $room->site_name,
            'building_id' => $room->building_id,
            'building_name' => $room->building_name,
            'floor_id' => $room->floor_id,
            'floor_alias' => $room->floor_alias,
            'floor_number' => $room->floor_number,
            'stands_count' => $room->stands_count,
        ]);
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
        $hierarchical_data = $this->hierarchicalData();

        return collect([
            __('Site') => route('archiving.register.site.show', $hierarchical_data->get('site_id')),
            __('Building') => route('archiving.register.building.show', $hierarchical_data->get('building_id')),
            __('Floor') => route('archiving.register.floor.show', $hierarchical_data->get('floor_id')),
        ])->when($root, function ($collection) {
            return $collection->put(__('Room'), route('archiving.register.room.show', $this->id));
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
