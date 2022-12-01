<?php

namespace App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

/**
 * @see https://laravel.com/docs/9.x/providers
 * @see https://dzone.com/articles/how-to-use-laravel-macro-with-example
 * @see https://qirolab.com/posts/what-are-laravel-macros-and-how-to-extending-laravels-core-classes-using-macros
 */
class BuilderMacroServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
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
            'orderByOrLatest' => \App\Macros\OrderByOrLatest::class,
        ];
    }
}
