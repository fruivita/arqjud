<?php

namespace App\Models\Trait;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Mensagem de feedback ao usuário.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait Auditavel
{
    use LogsActivity;

    /**
     * Configurações do log de atividade.
     *
     * @see https://spatie.be/docs/laravel-activitylog/v4/advanced-usage/logging-model-events
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['created_at', 'updated_at', 'deleted_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName(class_basename($this));
    }

    /**
     * Campos customizados do log de atividade.
     *
     * @see https://spatie.be/docs/laravel-activitylog/v4/basic-usage/logging-activity#content-setting-custom-properties
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->matricula = auth()->user()?->matricula;
    }
}
