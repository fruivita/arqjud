<?php

namespace App\Http\Middleware;

use App\Services\Menu\Menu;
use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * @see https://inertiajs.com/
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => fn () => auth()->user()
                ? [
                    'user' => ['matricula' => auth()->user()->matricula],
                    'menu' => Menu::make()->gerar(),
                    'home' => route('home.show'),
                    'logout' => route('logout'),
                ]
                : null,

            'flash' => fn () => session()->has('feedback')
                ? session()->pull('feedback')
                : null,
        ]);
    }
}
