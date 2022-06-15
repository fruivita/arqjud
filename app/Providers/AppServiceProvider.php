<?php

namespace App\Providers;

use App\View\Composers\DocumentationComposer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('components.footer', DocumentationComposer::class);

        Builder::macro('whereLike', function($columns, $value) {
            $this->when($value, function(Builder $query, $value) use ($columns) {

                $query->where(function (Builder $query) use ($columns, $value) {
                    foreach (Arr::wrap($columns) as $column) {
                        $query->orWhere($column, 'like', "%{$value}%");
                    }
                });

            });

            return $this;
        });
    }
}
