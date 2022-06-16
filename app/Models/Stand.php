<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Stand extends Model
{
    use HasFactory;

    protected $table = 'stands';

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
     * All stands.
     *
     * Extra columns:
     * - site_name: parent site name
     * - building_name: parent building name
     * - floor_number: parent floor number
     * - room_number: parent room number
     * - shelves_count: child shelves count
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public static function hierarchy()
    {
        return
        self::join('rooms', 'stands.room_id', '=', 'rooms.id')
        ->join('floors', 'rooms.floor_id', '=', 'floors.id')
        ->join('buildings', 'floors.building_id', '=', 'buildings.id')
        ->join('sites', 'buildings.site_id', '=', 'sites.id')
        ->leftJoin('shelves', 'shelves.stand_id', '=', 'stands.id')
        ->select([
            'stands.*',
            'rooms.number as room_number',
            'floors.number as floor_number',
            'buildings.name as building_name',
            'sites.name as site_name',
            DB::raw('COUNT(shelves.stand_id) as shelves_count')
        ])
        ->groupBy('stands.id');
    }

    /**
     * Get the stand's number ready to show on page.
     *
     * @return string
     */
    public function numberForHumans()
    {
        return standForHumans($this->number);
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
        $stand = new self();
        $stand->number = 0;
        $stand->description = __('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored');

        return $stand;
    }
}
