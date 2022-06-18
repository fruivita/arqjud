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
            'floors.number as floor_number',
            'rooms.number as room_number',
            DB::raw('COUNT(shelves.stand_id) as shelves_count')
        ])
        ->groupBy('stands.id');
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
        return collect([
            __('Site') => route('archiving.register.site.show', $this->site_id),
            __('Building') => route('archiving.register.building.show', $this->building_id),
            __('Floor') => route('archiving.register.floor.show', $this->floor_id),
            __('Room') => route('archiving.register.room.show', $this->room_id),
        ])->when($root, function ($collection) {
            return $collection->put(__('Stand'), route('archiving.register.stand.show', $this->id));
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
