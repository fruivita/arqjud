<?php

namespace App\Providers;

use App\Faker\NumeroProcessoCNJProvider;
use App\Faker\NumeroProcessoV1Provider;
use App\Faker\NumeroProcessoV2Provider;
use Faker\Generator;
use Illuminate\Support\ServiceProvider;

/**
 * @see https://laravel.com/docs/9.x/providers
 * @see https://hofmannsven.com/2021/faker-provider-in-laravel
 * @see https://github.com/laravel/framework/issues/42988
 */
class FakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->afterResolving(function (mixed $instance) {
            if ($instance instanceof Generator) {
                $instance->addProvider(new NumeroProcessoCNJProvider($instance));
                $instance->addProvider(new NumeroProcessoV1Provider($instance));
                $instance->addProvider(new NumeroProcessoV2Provider($instance));
            }
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
