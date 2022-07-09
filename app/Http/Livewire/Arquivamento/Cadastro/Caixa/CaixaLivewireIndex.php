<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Caixa;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Http\Livewire\Traits\ComPesquisa;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Models\Caixa;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class CaixaLivewireIndex extends Component
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
            'caixa',
            'ano',
            'qtd_volumes',
            'localidade',
            'predio',
            'andar',
            'sala',
            'estante',
            'prateleira',
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
        $this->authorize(Policy::ViewAny->value, Caixa::class);
    }

    /**
     * Computed property para listar de modo paginado as caixas.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCaixasProperty()
    {
        return
        Caixa::hierarquia()
            ->orWhereLike(['caixas.numero', 'caixas.ano'], $this->termo)
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
        return view('livewire.arquivamento.cadastro.caixa.index')->layout('layouts.app');
    }

    /**
     * Marca o item para ser excluído e aciona o modal de
     * confirmação.
     *
     * @param \App\Models\Caixa $caixa
     *
     * @return void
     */
    public function marcarParaExcluir(Caixa $caixa)
    {
        $this->confirmarExclusao($caixa);
    }
}
