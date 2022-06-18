<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/eloquent
 */
class BoxVolume extends Model
{
    use HasFactory;

    protected $table = 'box_volumes';

    /**
     * Relationship box volumes (N:1) box.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id', 'id');
    }

    /**
     * All box volumes.
     *
     * Extra columns:
     * - site_id: parent site id
     * - site_name: parent site name
     * - building_id: parent building id
     * - building_name: parent building name
     * - floor_id: parent floor id
     * - floor_number: parent floor number
     * - room_id: parent room id
     * - room_number: parent room number
     * - stand_id: parent stand id
     * - stand_number: parent stand number
     * - shelf_id: parent shelf id
     * - shelf_number: parent shelf number
     * - box_number: parent box number
     * - box_year: parent box year
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function hierarchy()
    {
        return
        self::join('boxes', 'box_volumes.box_id', '=', 'boxes.id')
        ->join('shelves', 'boxes.shelf_id', '=', 'shelves.id')
        ->join('stands', 'shelves.stand_id', '=', 'stands.id')
        ->join('rooms', 'stands.room_id', '=', 'rooms.id')
        ->join('floors', 'rooms.floor_id', '=', 'floors.id')
        ->join('buildings', 'floors.building_id', '=', 'buildings.id')
        ->join('sites', 'buildings.site_id', '=', 'sites.id')
        ->select([
            'box_volumes.*',
            'sites.id as site_id',
            'sites.name as site_name',
            'buildings.id as building_id',
            'buildings.name as building_name',
            'floors.id as floor_id',
            'floors.number as floor_number',
            'rooms.id as room_id',
            'rooms.number as room_number',
            'stands.id as stand_id',
            'stands.number as stand_number',
            'shelves.id as shelf_id',
            'shelves.number as shelf_number',
            'boxes.number as box_number',
            'boxes.year as box_year',
        ])
        ->groupBy('box_volumes.id');
    }

    /**
     * Get the box volume in human-readable format.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function forHumans(): Attribute
    {
        return Attribute::make(
            get: fn () => boxVolumeForHumans($this->number)
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
            get: fn () => standForHumans($this->stand_number)
        );
    }

    /**
     * Get the shelf in human-readable format.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function shelfForHumans(): Attribute
    {
        return Attribute::make(
            get: fn () => shelfForHumans($this->shelf_number)
        );
    }

    /**
     * Get the box in human-readable format.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function boxForHumans(): Attribute
    {
        return Attribute::make(
            get: fn () => boxForHumans($this->box_number, $this->box_year)
        );
    }
}
