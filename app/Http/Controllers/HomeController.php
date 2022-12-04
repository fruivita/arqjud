<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://inertiajs.com/
 */
class HomeController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return \Inertia\Response
     */
    public function show()
    {
        return Inertia::render('Home/HomeProcesso');
    }
}
