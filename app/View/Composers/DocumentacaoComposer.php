<?php

namespace App\View\Composers;

use App\Models\Documentacao;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/**
 * @see https://laravel.com/docs/views#view-composers
 */
class DocumentacaoComposer
{
    /**
     * Cria um novo profile composer.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Disponibiliza os dados para a view.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $view->with(['doc_link' => $this->getDocLink()]);
    }

    /**
     * Gera o link para a documentação de acordo com a rota visitada pelo
     * usuário.
     *
     * @return string url/link para a documentação
     */
    private function getDocLink()
    {
        $doc_link = optional(
            Documentacao::firstWhere('app_link', Route::currentRouteName())
        )->doc_link;

        return $doc_link ?? config('app.doc_link_default');
    }
}
