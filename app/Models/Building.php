<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Building extends Model
{
    use HasFactory;

    protected $table = 'buildings';

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
     * All buildings.
     *
     * Extra columns:
     * - site_name: parent site name
     * - floors_count: child floors count
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function hierarchy()
    {
        return
        self::join('sites', 'buildings.site_id', '=', 'sites.id')
        ->leftJoin('floors', 'floors.building_id', '=', 'buildings.id')
        ->select([
            'buildings.*',
            'sites.name as site_name',
            DB::raw('COUNT(floors.building_id) as floors_count')
        ])
        ->groupBy('buildings.id');
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
        ])->when($root, function ($collection) {
            return $collection->put(__('Building'), route('archiving.register.building.show', $this->id));
        });
    }
}
