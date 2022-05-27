<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Box extends Model
{
    use HasFactory;

    protected $table = 'boxes';

    protected $fillable = ['year', 'number', 'stand', 'shelf'];

    /**
     * Relationship box (N:1) room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
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
    public function scopeDefaultOrder($query)
    {
        return $query
        ->orderBy('year', 'desc')
        ->orderBy('number', 'desc');
    }

    /**
     * Previous record.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function previous()
    {
        return self::select('id')
        ->whereRaw('year >= (select year from boxes where id = ?)', [$this->id])
        ->where(function ($query) {
            return $query
            ->whereRaw('year > (select year from boxes where id = ?)', [$this->id])
            ->orWhereRaw('number > (select number from boxes where id = ?)', [$this->id]);
        })
        ->orderBy('year', 'asc')
        ->orderBy('number', 'asc')
        ->take(1);
    }

    /**
     * Next record.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function next()
    {
        return self::select('id', 'number')
        ->whereRaw('year <= (select year from boxes where id = ?)', [$this->id])
        ->where(function ($query) {
            return $query
            ->whereRaw('year < (select year from boxes where id = ?)', [$this->id])
            ->orWhereRaw('number < (select number from boxes where id = ?)', [$this->id]);
        })
        ->orderBy('year', 'desc')
        ->orderBy('number', 'desc')
        ->take(1);
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
     * Creates several boxes in the informed room and persists them in the
     * database.
     *
     * @param \App\Models\Box  $template template for creating the boxes
     * @param int              $amount   number of boxes to create
     * @param \App\Models\Room $room
     *
     * @return bool
     */
    public static function createMany(Box $template, int $amount, Room $room)
    {
        try {
            DB::transaction(function () use ($template, $amount, $room) {
                self::insert(
                    self::generateMany($template, $amount, $room)->toArray()
                );
            });

            return true;
        } catch (\Throwable $th) {
            Log::error(
                __('Box creation failed'),
                [
                    'template' => $template,
                    'amount' => $amount,
                    'room' => $room,
                    'exception' => $th,
                ]
            );

            return false;
        }
    }

    /**
     * Generates a collection with all attributes of the boxes cloned from the
     * box template and linked to the room.
     *
     * The number of the first box will be the one defined in the template and
     * the others will be increments by one.
     *
     * @param \App\Models\Box  $template template for creating the boxes
     * @param int              $amount   number of boxes to create
     * @param \App\Models\Room $room     boxes room
     *
     * @return \Illuminate\Support\Collection
     */
    private static function generateMany(Box $template, int $amount, Room $room)
    {
        return
        collect()
        ->range($template->number, $template->number + $amount - 1)
        ->map(function($value) use($template, $room) {
            return [
                'year' => $template->year,
                'number' => $value,
                'stand' => $template->stand,
                'shelf' => $template->shelf,
                'room_id' => $room->id,
            ];
        });
    }
}
