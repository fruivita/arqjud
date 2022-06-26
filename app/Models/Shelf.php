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
class Shelf extends Model
{
    use HasFactory;
    use Humanize;

    /**
     * The table associated with the model.
     *
     * @var string
     */
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
     * All shelves.
     *
     * Extra columns:
     * - site_id: parent site id
     * - site_name: parent site name
     * - building_id: parent building id
     * - building_name: parent building name
     * - floor_id: parent floor id
     * - floor_alias: parent floor alias
     * - floor_number: parent floor number
     * - room_id: parent room id
     * - room_number: parent room number
     * - stand_number: parent stand number
     * - boxes_count: child boxes count
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function hierarchy()
    {
        return
        self::join('stands', 'shelves.stand_id', '=', 'stands.id')
        ->join('rooms', 'stands.room_id', '=', 'rooms.id')
        ->join('floors', 'rooms.floor_id', '=', 'floors.id')
        ->join('buildings', 'floors.building_id', '=', 'buildings.id')
        ->join('sites', 'buildings.site_id', '=', 'sites.id')
        ->leftJoin('boxes', 'boxes.shelf_id', '=', 'shelves.id')
        ->select([
            'shelves.*',
            'sites.id as site_id',
            'sites.name as site_name',
            'buildings.id as building_id',
            'buildings.name as building_name',
            'floors.id as floor_id',
            'floors.alias as floor_alias',
            'floors.number as floor_number',
            'rooms.id as room_id',
            'rooms.number as room_number',
            'stands.number as stand_number',
            DB::raw('COUNT(boxes.shelf_id) as boxes_count')
        ])
        ->groupBy('shelves.id');
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
     * - stand_id: parent stand id
     * - stand_number: parent stand number
     * - boxes_count: child boxes count
     *
     * @return \Illuminate\Support\Collection
     */
    private function hierarchicalData()
    {
        $shelf = isset($this->site_name)
        ? $this
        : self::hierarchy()->find($this->id);

        return collect([
            'site_id' => $shelf->site_id,
            'site_name' => $shelf->site_name,
            'building_id' => $shelf->building_id,
            'building_name' => $shelf->building_name,
            'floor_id' => $shelf->floor_id,
            'floor_alias' => $shelf->floor_alias,
            'floor_number' => $shelf->floor_number,
            'room_id' => $shelf->room_id,
            'room_number' => $shelf->room_number,
            'stand_id' => $shelf->stand_id,
            'stand_number' => $shelf->stand_number,
            'boxes_count' => $shelf->boxes_count,
        ]);
    }

    /**
     * Get the shelf in human-readable format.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function forHumans(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->humanizeShelf($this->number)
        );
    }

    /**
     * Get the stand in human-readable format.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function standForHumans(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->humanizeStand($this->stand_number)
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
            __('Stand') => route('archiving.register.stand.edit', $hierarchical_data->get('stand_id')),
        ])->when($root, function ($collection) {
            return $collection->put(__('Shelf'), route('archiving.register.shelf.edit', $this->id));
        });
    }

    /**
     * Default shelf to be used when creating a stand.
     *
     * @return self
     */
    public static function uninformedShelf()
    {
        $shelf = new self();
        $shelf->number = 0;
        $shelf->description = __('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored');

        return $shelf;
    }
}
