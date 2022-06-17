<?php

namespace App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

/**
 * @see https://laravel.com/docs/9.x/providers
 */
class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        Collection::make($this->builderMacros())
            ->reject(fn ($class, $macro) => Builder::hasMacro($macro))
            ->each(fn ($class, $macro) => Builder::macro($macro, app($class)()));
    }

    /**
     * Macros for Query Builder.
     *
     * @return array<string, class-string>
     */
    private function builderMacros()
    {
        return [
            'orderByWhen' => \App\Macros\OrderByWhen::class,
            'whereLike' => \App\Macros\WhereLike::class,
        ];
    }
}
