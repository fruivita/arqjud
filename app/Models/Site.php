<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Site extends Model
{
    use HasFactory;

    protected $table = 'sites';

    /**
     * Relationship site (1:N) buildings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function buildings()
    {
        return $this->hasMany(Building::class, 'site_id', 'id');
    }

    /**
     * All sites.
     *
     * Extra columns:
     * - buildings_count: child buildings count
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public static function hierarchy()
    {
        return
        self::leftJoin('buildings', 'buildings.site_id', '=', 'sites.id')
        ->select([
            'sites.*',
            DB::raw('COUNT(buildings.site_id) as buildings_count')
        ])
        ->groupBy('sites.id');
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
        return collect()->when($root, function ($collection) {
            return $collection->put(__('Site'), route('archiving.register.site.show', $this));
        });
    }
}
