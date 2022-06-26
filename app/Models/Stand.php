<?php

namespace App\Models;

use App\Models\Traits\Humanize;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Stand extends Model
{
    use HasFactory;
    use Humanize;

    /**
     * The table associated with the model.
     *
     * @var string
     */
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
     * - site_id: parent site id
     * - site_name: parent site name
     * - building_id: parent building id
     * - building_name: parent building name
     * - floor_id: parent floor id
     * - floor_number: parent floor number
     * - floor_alias: parent floor alias
     * - room_number: parent room number
     * - shelves_count: child shelves count
     *
     * @return \Illuminate\Database\Query\Builder
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
            'sites.id as site_id',
            'sites.name as site_name',
            'buildings.id as building_id',
            'buildings.name as building_name',
            'floors.id as floor_id',
            'floors.alias as floor_alias',
            'floors.number as floor_number',
            'rooms.number as room_number',
            DB::raw('COUNT(shelves.stand_id) as shelves_count')
        ])
        ->groupBy('stands.id');
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
     * - floor_alias: parent floor alias
     * - floor_number: parent floor number
     * - room_id: parent room id
     * - room_number: parent room number
     * - shelves_count: child shelves count
     *
     * @return \Illuminate\Support\Collection
     */
    private function hierarchicalData()
    {
        $stand = isset($this->site_name)
        ? $this
        : self::hierarchy()->find($this->id);

        return collect([
            'site_id' => $stand->site_id,
            'site_name' => $stand->site_name,
            'building_id' => $stand->building_id,
            'building_name' => $stand->building_name,
            'floor_id' => $stand->floor_id,
            'floor_alias' => $stand->floor_alias,
            'floor_number' => $stand->floor_number,
            'room_id' => $stand->room_id,
            'room_number' => $stand->room_number,
            'shelves_count' => $stand->shelves_count,
        ]);
    }

    /**
     * Get the stand in human-readable format.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function forHumans(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->humanizeStand($this->number)
        );
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
            __('Floor') => route('archiving.register.floor.edit', $hierarchical_data->get('floor_id')),
            __('Room') => route('archiving.register.room.edit', $hierarchical_data->get('room_id')),
        ])->when($root, function ($collection) {
            return $collection->put(__('Stand'), route('archiving.register.stand.edit', $this->id));
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
