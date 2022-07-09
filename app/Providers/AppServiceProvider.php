<?php

namespace App\Providers;

use App\View\Composers\DocumentacaoComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * @see https://laravel.com/docs/9.x/providers
 * @see https://laravel.com/docs/9.x/views#view-composers
 */
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
        View::composer('components.footer', DocumentacaoComposer::class);
    }
}
