<?php

namespace App\View\Components;

use App\Http\Traits\ComPaginacaoEmCache;
use Illuminate\View\Component;

/**
 * Compartilha com o frontend dados estáticos sobre a aplicação, isto é, dados
 * que não são alterados com o funcionamento rotineiro da aplicação.
 *
 * @link https://laravel.com/docs/9.x/blade
 * @link https://www.youtube.com/watch?v=IZIzcjDdPIw
 */
class DadosEstaticos extends Component
{
    use ComPaginacaoEmCache;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.dados-estaticos', [
            'dados' => [
                'app_nome' => config('app.name'),
                'app_nome_completo' => config('app.nome_completo'),
                'app_versao' => config('app.versao'),
                'orgao_sigla' => config('orgao.sigla'),
                'paginacao' => $this->getOpcoes(),
            ],
        ]);
    }
}
