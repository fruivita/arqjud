<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Estante;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Http\Livewire\Traits\ComPesquisa;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Models\Estante;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class EstanteLivewireIndex extends Component
{
    use AuthorizesRequests;
    use ComPreferencias;
    use ComExclusao;
    use ComFeedback;
    use ComPaginacao;
    use ComPesquisa;
    use ComOrdenacao;

    /**
     * Preferências do usuário.
     *
     * @var array<string, mixed>
     */
    public array $preferencias = [
        // Nome das colunas da tabela que podem ser ocultadas
        'colunas' => [
            'estante',
            'apelido',
            'qtd_prateleiras',
            'localidade',
            'predio',
            'andar',
            'sala',
            'acoes'
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
        $this->authorize(Policy::ViewAny->value, Estante::class);
    }

    /**
     * Computed property para listar de modo paginado as estantes.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getEstantesProperty()
    {
        return
        Estante::hierarquia()
            ->orWhereLike(['estantes.numero', 'estantes.apelido'], $this->termo)
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
        return view('livewire.arquivamento.cadastro.estante.index')->layout('layouts.app');
    }

    /**
     * Marca o item para ser excluído e aciona o modal de
     * confirmação.
     *
     * @param \App\Models\Estante $estante
     *
     * @return void
     */
    public function marcarParaExcluir(Estante $estante)
    {
        $this->confirmarExclusao($estante);
    }
}
