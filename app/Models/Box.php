<?php

namespace App\Models;

use App\Models\Traits\Humanize;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Box extends Model
{
    use HasFactory;
    use Humanize;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'boxes';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['year', 'number', 'description'];

    /**
     * Relationship box (N:1) shelf.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shelf()
    {
        return $this->belongsTo(Shelf::class, 'shelf_id', 'id');
    }

    /**
     * Relationship box (1:N) box volumes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function volumes()
    {
        return $this->hasMany(BoxVolume::class, 'box_id', 'id');
    }

    /**
     * All boxes.
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
     * - stand_id: parent stand id
     * - stand_number: parent stand number
     * - shelf_number: parent shelf number
     * - volumes_count: child box volumes count
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function hierarchy()
    {
        return
        self::join('shelves', 'boxes.shelf_id', '=', 'shelves.id')
        ->join('stands', 'shelves.stand_id', '=', 'stands.id')
        ->join('rooms', 'stands.room_id', '=', 'rooms.id')
        ->join('floors', 'rooms.floor_id', '=', 'floors.id')
        ->join('buildings', 'floors.building_id', '=', 'buildings.id')
        ->join('sites', 'buildings.site_id', '=', 'sites.id')
        ->leftJoin('box_volumes', 'box_volumes.box_id', '=', 'boxes.id')
        ->select([
            'boxes.*',
            'sites.id as site_id',
            'sites.name as site_name',
            'buildings.id as building_id',
            'buildings.name as building_name',
            'floors.id as floor_id',
            'floors.alias as floor_alias',
            'floors.number as floor_number',
            'rooms.id as room_id',
            'rooms.number as room_number',
            'stands.id as stand_id',
            'stands.number as stand_number',
            'shelves.number as shelf_number',
            DB::raw('COUNT(box_volumes.box_id) as volumes_count')
        ])
        ->groupBy('boxes.id');
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
     * - shelf_id: parent shelf id
     * - shelf_number: parent shelf number
     * - volumes_count: child box volumes count
     *
     * @return \Illuminate\Support\Collection
     */
    private function hierarchicalData()
    {
        $box = isset($this->site_name)
        ? $this
        : self::hierarchy()->find($this->id);

        return collect([
            'site_id' => $box->site_id,
            'site_name' => $box->site_name,
            'building_id' => $box->building_id,
            'building_name' => $box->building_name,
            'floor_id' => $box->floor_id,
            'floor_alias' => $box->floor_alias,
            'floor_number' => $box->floor_number,
            'room_id' => $box->room_id,
            'room_number' => $box->room_number,
            'stand_id' => $box->stand_id,
            'stand_number' => $box->stand_number,
            'shelf_id' => $box->shelf_id,
            'shelf_number' => $box->shelf_number,
            'volumes_count' => $box->volumes_count,
        ]);
    }

    /**
     * Get the box in human-readable format.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function forHumans(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->humanizeBox($this->number, $this->year)
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
     * Get the shelf in human-readable format.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function shelfForHumans(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->humanizeShelf($this->shelf_number)
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
            __('Site') => route('archiving.register.site.show', $hierarchical_data->get('site_id')),
            __('Building') => route('archiving.register.building.show', $hierarchical_data->get('building_id')),
            __('Floor') => route('archiving.register.floor.show', $hierarchical_data->get('floor_id')),
            __('Room') => route('archiving.register.room.show', $hierarchical_data->get('room_id')),
            __('Stand') => route('archiving.register.stand.show', $hierarchical_data->get('stand_id')),
            __('Shelf') => route('archiving.register.shelf.show', $hierarchical_data->get('shelf_id')),
        ])->when($root, function ($collection) {
            return $collection->put(__('Box'), route('archiving.register.box.show', $this->id));
        });
    }

    /**
     * Generates the next box volume number.
     *
     * @return int
     */
    public function nextVolumeNumber()
    {
        return $this->volumes()->max('number') + 1;
    }

    /**
     * Generates the next box number.
     *
     * @return int
     */
    public static function nextBoxNumber(int $year)
    {
        return self::where('year', $year)->max('number') + 1;
    }

    /**
     * Creates several boxes as child of the informed shelf and persists them
     * in the database.
     *
     * @param \App\Models\Box   $template template for creating the boxes
     * @param int               $amount   number of boxes to create
     * @param int               $volumes  number of box volumes
     * @param \App\Models\Shelf $shelf    parent element
     *
     * @return bool
     */
    public static function createMany(Box $template, int $amount, int $volumes, Shelf $shelf)
    {
        try {
            DB::transaction(function () use ($template, $amount, $volumes, $shelf) {
                $boxes = self::generateMany($template, $amount, $shelf);

                self::insert($boxes->toArray());

                $boxes_id = self::lastInsertedIds($boxes);

                BoxVolume::insert(
                    self::generateBoxVolumes($volumes, $boxes_id)->toArray()
                );
            });

            return true;
        } catch (\Throwable $th) {
            Log::error(
                __('Box creation failed'),
                [
                    'template' => $template,
                    'amount' => $amount,
                    'volumes' => $volumes,
                    'shelf' => $shelf,
                    'exception' => $th,
                ]
            );

            return false;
        }
    }

    /**
     * Generates a collection with all attributes of the boxes 'cloned' from
     * the box template and as a child of the shelf.
     *
     * The number of the first box will be the one defined in the template and
     * the others will be increments by one.
     *
     * @param \App\Models\Box   $template template for creating the boxes
     * @param int               $amount   number of boxes to create
     * @param \App\Models\Shelf $shelf    parent of all boxes
     *
     * @return \Illuminate\Support\Collection
     */
    private static function generateMany(Box $template, int $amount, Shelf $shelf)
    {
        return
        collect()
        ->range($template->number, $template->number + $amount - 1)
        ->map(function ($value) use ($template, $shelf) {
            return [
                'year' => $template->year,
                'number' => $value,
                'description' => $template->description,
                'shelf_id' => $shelf->id,
            ];
        });
    }

    /**
     * Id of all boxes created.
     *
     * @param \Illuminate\Support\Collection $boxes boxes, before persistence,
     *                                              that were registered
     *
     * @return \Illuminate\Support\Collection
     */
    private static function lastInsertedIds(Collection $boxes)
    {
        return self::select('id')
        ->where('year', $boxes->first()['year'])
        ->whereBetween('number', [$boxes->first()['number'], $boxes->last()['number']])
        ->get();
    }

    /**
     * Generates a certain amount of volumes as a child of the informed boxes.
     *
     * The number of volumes in each box is the same and, in each box, the
     * volume identification number is incremented by 1 from 1.
     *
     * @param int                            $amount number of box volumes
     * @param \Illuminate\Support\Collection $boxes
     *
     * @return \Illuminate\Support\Collection
     */
    private static function generateBoxVolumes(int $amount, Collection $boxes)
    {
        return
        $boxes
        ->pluck('id')
        ->map(function ($box_id) use ($amount) {
            return collect()
            ->range(1, $amount)
            ->map(function ($value) use ($box_id) {
                return [
                    'number' => $value,
                    'alias' => "Vol. {$value}",
                    'box_id' => $box_id,
                ];
            });
        })->flatten(1);
    }
}
