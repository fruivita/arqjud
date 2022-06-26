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

    /**
     * The table associated with the model.
     *
     * @var string
     */
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
     * - site_id: parent site id
     * - site_name: parent site name
     * - building_name: parent building name
     * - rooms_count: child rooms count
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function hierarchy()
    {
        return
        self::join('buildings', 'floors.building_id', '=', 'buildings.id')
        ->join('sites', 'buildings.site_id', '=', 'sites.id')
        ->leftJoin('rooms', 'rooms.floor_id', '=', 'floors.id')
        ->select([
            'floors.*',
            'sites.id as site_id',
            'sites.name as site_name',
            'buildings.name as building_name',
            DB::raw('COUNT(rooms.floor_id) as rooms_count')
        ])
        ->groupBy('floors.id');
    }

    /**
     * Model hierarchical fields.
     *
     * keys:
     * - site_id: parent site id
     * - site_name: parent site name
     * - building_id: parent building id
     * - building_name: parent building name
     * - rooms_count: child rooms count
     *
     * @return \Illuminate\Support\Collection
     */
    private function hierarchicalData()
    {
        $floor = isset($this->site_name)
        ? $this
        : self::hierarchy()->find($this->id);

        return collect([
            'site_id' => $floor->site_id,
            'site_name' => $floor->site_name,
            'building_id' => $floor->building_id,
            'building_name' => $floor->building_name,
            'rooms_count' => $floor->rooms_count,
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
            __('Site') => route('archiving.register.site.edit', $hierarchical_data->get('site_id')),
            __('Building') => route('archiving.register.building.edit', $hierarchical_data->get('building_id')),
        ])->when($root, function ($collection) {
            return $collection->put(__('Floor'), route('archiving.register.floor.edit', $this->id));
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
