<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Atividade extends Activity
{
    /**
     * Pesquisa utilizando o termo informado com o operador like no seguinte
     * formato: `termo%`
     *
     * @return void
     */
    public function scopeSearch(Builder $query, string $termo = null)
    {
        $termo = "{$termo}%";

        $query->where(function (Builder $query) use ($termo) {
            $query->where('activity_log.log_name', 'like', $termo)
                ->orWhere('activity_log.description', 'like', $termo)
                ->orWhere('activity_log.event', 'like', $termo)
                ->orWhere('activity_log.matricula', 'like', $termo)
                ->orWhere('activity_log.batch_uuid', 'like', $termo);
        });
    }
}
