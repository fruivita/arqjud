<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
     * All shelves.
     *
     * Extra columns:
     * - site_name: parent site name
     * - building_name: parent building name
     * - floor_number: parent floor number
     * - room_number: parent room number
     * - stand_number: parent stand number
     * - boxes_count: child boxes count
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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
            'stands.number as stand_number',
            'rooms.number as room_number',
            'floors.number as floor_number',
            'buildings.name as building_name',
            'sites.name as site_name',
            DB::raw('COUNT(boxes.shelf_id) as boxes_count')
        ])
        ->groupBy('shelves.id');
    }

    /**
     * Get the shelf's number ready to show on page.
     *
     * @return string
     */
    public function numberForHumans()
    {
        return shelfForHumans($this->number);
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
            __('Site') => route('archiving.register.site.show', $this->stand->room->floor->building->site),
            __('Building') => route('archiving.register.building.show', $this->stand->room->floor->building),
            __('Floor') => route('archiving.register.floor.show', $this->stand->room->floor),
            __('Room') => route('archiving.register.room.show', $this->stand->room),
            __('Stand') => route('archiving.register.stand.show', $this->stand),
        ])->when($root, function ($collection) {
            return $collection->put(__('Shelf'), route('archiving.register.shelf.show', $this));
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
