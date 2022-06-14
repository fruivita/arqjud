<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
     * Box name.
     *
     * @return string
     */
    public function name()
    {
        return $this->number . '/' . $this->year;
    }

    /**
     * Default ordering of the model.
     *
     * Order: year desc
     * Order: number desc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultOrder(Builder $query)
    {
        return $query
        ->orderBy('year', 'desc')
        ->orderBy('number', 'desc');
    }

    /**
     * Records filtered by the term entered.
     *
     * The filter applies to the number and the year through the OR clause.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null                           $term
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeSearch(Builder $query, string $term = null)
    {
        return $query->when($term, function ($query, $term) {
            $query
                ->where('number', 'like', "%{$term}%")
                ->orWhere('year', 'like', "%{$term}%");
        });
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
            __('Site') => route('archiving.register.site.show', $this->shelf->stand->room->floor->building->site),
            __('Building') => route('archiving.register.building.show', $this->shelf->stand->room->floor->building),
            __('Floor') => route('archiving.register.floor.show', $this->shelf->stand->room->floor),
            __('Room') => route('archiving.register.room.show', $this->shelf->stand->room),
            __('Stand') => route('archiving.register.stand.show', $this->shelf->stand),
            __('Shelf') => route('archiving.register.shelf.show', $this->shelf),
        ])->when($root, function ($collection) {
            return $collection->put(__('Box'), route('archiving.register.box.show', $this));
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
                    'box_id' => $box_id,
                ];
            });
        })->flatten(1);
    }
}
