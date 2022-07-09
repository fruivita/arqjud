<?php

namespace App\Http\Livewire\Autorizacao\Perfil;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Http\Livewire\Traits\ComLimite;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComPesquisa;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Models\Perfil;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class PerfilLivewireIndex extends Component
{
    use AuthorizesRequests;
    use ComPreferencias;
    use ComLimite;
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
            'perfil',
            'permissoes',
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
        $this->authorize(Policy::ViewAny->value, Perfil::class);
    }

    /**
     * Computed property para listar de modo paginado os perfis e as permissões
     * que elas são usadas.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPerfisProperty()
    {
        return
        Perfil::with(['permissoes' => function ($query) {
            $query->ordenacaoPadrao()->limit($this->limite);
        }])
        ->orWhereLike(['nome'], $this->termo)
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
        return view('livewire.autorizacao.perfil.index')->layout('layouts.app');
    }
}
