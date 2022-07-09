<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Sala;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Http\Livewire\Traits\ComPesquisa;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Models\Sala;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class SalaLivewireIndex extends Component
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
            'sala',
            'qtd_estantes',
            'localidade',
            'predio',
            'andar',
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
        $this->authorize(Policy::ViewAny->value, Sala::class);
    }

    /**
     * Computed property para listar de modo paginado as salas.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getSalasProperty()
    {
        return
        Sala::hierarquia()
            ->orWhereLike('salas.numero', $this->termo)
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
        return view('livewire.arquivamento.cadastro.sala.index')->layout('layouts.app');
    }

    /**
     * Marca o item para ser excluído e aciona o modal de
     * confirmação.
     *
     * @param \App\Models\Sala $sala
     *
     * @return void
     */
    public function marcarParaExcluir(Sala $sala)
    {
        $this->confirmarExclusao($sala);
    }
}
