<?php

namespace App\Http\Livewire\Autorizacao\Delegacao;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Http\Livewire\Traits\ComPesquisa;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Models\Usuario;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class DelegacaoLivewireIndex extends Component
{
    use AuthorizesRequests;
    use ComPreferencias;
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
            'nome',
            'usuario',
            'perfil',
            'delegante',
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
        $this->authorize(Policy::DelegacaoViewAny->value);
    }

    /**
     * Computed property para listar de modo paginado os usuários delegáveis.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getDelegaveisProperty()
    {
        return
        Usuario::with('delegante')
            ->orWhereLike(['nome', 'username'], $this->termo)
            ->where('lotacao_id', auth()->user()->lotacao_id)
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
        return view('livewire.autorizacao.delegacao.index')->layout('layouts.app');
    }

    /**
     * Cria a delegação atribuindo ao usuário informado o mesmo perfil do
     * usuário autenticado.
     *
     * @param \App\Models\Usuario $delegado
     *
     * @return void
     */
    public function create(Usuario $delegado)
    {
        $this->authorize(Policy::DelegacaoCreate->value, [$delegado]);

        auth()->user()->delegar($delegado);
    }

    /**
     * Desfaz a delegação atribuindo ao usuário informado seu perfil anterior.
     *
     * @param \App\Models\Usuario $delegado
     *
     * @return void
     */
    public function destroy(Usuario $delegado)
    {
        $this->authorize(Policy::DelegacaoDelete->value, [$delegado]);

        $delegado->revogaDelegacao();
    }
}
