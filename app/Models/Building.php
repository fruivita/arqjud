<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Building extends Model
{
    use HasFactory;

    protected $table = 'buildings';

    /**
     * The relationship counts that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withCount = ['floors'];

    /**
     * Relationship building (N:1) site.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }

    /**
     * Relationship building (1:N) floors.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function floors()
    {
        return $this->hasMany(Floor::class, 'building_id', 'id');
    }

    /**
     * Default ordering of the model.
     *
     * Order: name asc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultOrder($query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * Previous record based on defaultOrder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function previous()
    {
        return self::select('id')
        ->whereRaw('name < (select name from buildings where id = ?)', [$this->id])
        ->orderBy('name', 'desc')
        ->take(1);
    }

    /**
     * Next record based on defaultOrder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function next()
    {
        return self::select('id')
        ->whereRaw('name > (select name from buildings where id = ?)', [$this->id])
        ->orderBy('name', 'asc')
        ->take(1);
    }

    /**
     * Links to the parent entities.
     *
     * @return \Illuminate\Support\Collection
     */
    public function parentEntitiesLinks()
    {
        return collect([
            __('Site') => route('archiving.register.site.show', $this->site),
        ]);
    }
}
