<?php

namespace App\Http\Controllers\Administracao;

use App\Enums\Policy;
use App\Enums\Queue;
use App\Http\Controllers\Controller;
use App\Http\Requests\Atendimento\StoreSolicitacaoRequest;
use App\Http\Resources\Solicitacao\CounterResource;
use App\Http\Resources\Solicitacao\SolicitacaoCollection;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Jobs\ImportarDadosRH;
use App\Models\Lotacao;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Pipes\Importacao\Importar;
use App\Pipes\Search;
use App\Pipes\Solicitacao\JoinAll;
use App\Pipes\Solicitacao\NotificarSolicitante;
use App\Pipes\Solicitacao\Order;
use App\Pipes\Solicitacao\SolicitarProcesso;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/controllers
 * @see https://inertiajs.com/server-side-setup
 */
class ImportacaoController extends Controller
{
    use ComFeedback;

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $this->authorize(Policy::ImportacaoCreate->value);

        return Inertia::render('Administracao/Importacao/Create', [
            'links' => fn () => [
                'store' => route('administracao.importacao.store'),
            ],
            'opcoes' => [['id' => 'rh', 'nome' => 'Dados do RH']],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize(Policy::ImportacaoCreate->value);

        $importacao = new \stdClass();
        $importacao->importacoes = $request->input('importacoes');

        $obj = Pipeline::make()
            ->withTransaction()
            ->send($importacao)
            ->through([Importar::class])
            ->thenReturn();

        $feedback = $obj->importado
            ? __('Importação foi escalonada para execução. Em breve os dados estarão disponíveis.')
            : null;

        return back()->with($this->feedback($obj->importado, $feedback));
    }
}
