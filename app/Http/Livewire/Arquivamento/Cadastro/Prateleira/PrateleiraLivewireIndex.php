<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Prateleira;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComPesquisa;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Models\Prateleira;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class PrateleiraLivewireIndex extends Component
{
    use AuthorizesRequests;
    use ComExclusao;
    use ComFeedback;
    use ComOrdenacao;
    use ComPaginacao;
    use ComPesquisa;
    use ComPreferencias;

    /**
     * Preferências do usuário.
     *
     * @var array<string, mixed>
     */
    public array $preferencias = [
        // Nome das colunas da tabela que podem ser ocultadas
        'colunas' => [
            'prateleira',
            'apelido',
            'qtd_caixas',
            'localidade',
            'predio',
            'andar',
            'sala',
            'estante',
            'acoes',
        ],

        // Quantidade de registros exibidos por página da tabela
        'por_pagina' => 10,
    ];

    /**
     * Executado em cada request, imediatamente após o componente ser
     * instanciado, mas antes de qualquer outro método do ciclo de vida ser
     * acionado.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::ViewAny->value, Prateleira::class);
    }

    /**
     * Computed property para listar de modo paginado as prateleiras.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPrateleirasProperty()
    {
        return
        Prateleira::hierarquia()
            ->orWhereLike(['prateleiras.numero', 'prateleiras.apelido'], $this->termo)
            ->orderByWhen($this->ordenacoes)
            ->paginate($this->preferencias['por_pagina']);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.arquivamento.cadastro.prateleira.index')->layout('layouts.app');
    }

    /**
     * Marca o item para ser excluído e aciona o modal de
     * confirmação.
     *
     * @param \App\Models\Prateleira $prateleira
     *
     * @return void
     */
    public function marcarParaExcluir(Prateleira $prateleira)
    {
        $this->confirmarExclusao($prateleira);
    }
}
