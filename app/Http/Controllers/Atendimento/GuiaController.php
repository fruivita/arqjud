<?php

namespace App\Http\Controllers\Atendimento;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Resources\Guia\GuiaCollection;
use App\Http\Resources\Guia\GuiaResource;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Guia;
use App\Pipes\Guia\Order;
use App\Pipes\Search;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/controllers
 * @see https://inertiajs.com/server-side-setup
 */
class GuiaController extends Controller
{
    use ComPaginacaoEmCache;

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Atendimento/Guia/Index', [
            'guias' => fn () => GuiaCollection::make(
                Pipeline::make()
                    ->send(Guia::query())
                    ->through([Order::class, Search::class])
                    ->thenReturn()
                    ->paginate($this->perPage())
            )->additional(['meta' => [
                'termo' => request()->query('termo'),
                'order' => request()->query('order'),
            ]])->preserveQuery(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Inertia\Response
     */
    public function show(Guia $guia)
    {
        $this->authorize(Policy::View->value, Guia::class);

        return Inertia::render('Atendimento/Guia/Show', [
            'guia' => GuiaResource::make($guia),
        ]);
    }

    /**
     * Display (PDF) the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pdf(Guia $guia)
    {
        $this->authorize(Policy::View->value, Guia::class);

        $pdf = Pdf::loadView('pdf.guia', [
            'cabecalho' => __('Guia de remessa de processos'),
            'guia' => $guia,
        ]);
        $pdf->render();
        injetarTotalPagina($pdf);

        return $pdf->stream('guia.pdf');
    }
}
