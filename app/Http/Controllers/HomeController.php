<?php

namespace App\Http\Controllers;

use App\Enums\Policy;
use App\Http\Requests\ShowProcessoHomeRequest;
use App\Models\Processo;
use App\Models\Remessa;
use App\Services\Processo\PesquisarProcesso;
use App\Services\Remessa\PesquisarRemessa;
use Inertia\Inertia;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://inertiajs.com/
 */
class HomeController extends Controller
{
    /**
     * Display the specified resource.
     * @return \Inertia\Response
     */
    public function show()
    {
        return Inertia::render('Home/HomeProcesso');
    }
}
