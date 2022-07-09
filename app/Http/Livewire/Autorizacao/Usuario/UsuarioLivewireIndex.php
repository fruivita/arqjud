<?php

namespace App\Http\Livewire\Autorizacao\Usuario;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Http\Livewire\Traits\ComPesquisa;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Models\Perfil;
use App\Models\Usuario;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class UsuarioLivewireIndex extends Component
{
    use AuthorizesRequests;
    use ComPreferencias;
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
     * Item em edição.
     *
     * @var \App\Models\Usuario
     */
    public Usuario $em_edicao;

    /**
     * Perfis disponíveis.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    public $perfis;

    /**
     * Deve-se exibir o modal para edição do item?
     *
     * @var bool
     */
    public $exibir_modal_edicao = false;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'em_edicao.perfil_id' => [
                'bail',
                'required',
                'exists:perfis,id',
            ],
        ];
    }

    /**
     * Atributos customizados para os erros de validação.
     *
     * @return array<string, mixed>
     */
    protected function validationAttributes()
    {
        return [
            'em_edicao.perfil_id' => __('Perfil'),
        ];
    }

    /**
     * Executado em cada request, imediatamente após o componente ser
     * instanciado, mas antes de qualquer outro método do ciclo de vida ser
     * acionado.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::ViewAnyOrUpdate->value, Usuario::class);
    }

    /**
     * Executado uma única vez, imediatamente após o componente ser
     * instanciado, mas antes do método render() ser acionado. É acionado
     * apenas no carregamento inicial da página e nunca mais chamado, inclusive
     * nas atualizações do componente.
     *
     * @return void
     */
    public function mount()
    {
        $this->em_edicao = new Usuario();
    }

    /**
     * Computed property para listar de modo paginado os usuários e seus
     * perfis.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUsuariosProperty()
    {
        return
        Usuario::with('delegante')
            ->orWhereLike(['nome', 'username'], $this->termo)
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
        return view('livewire.autorizacao.usuario.index')->layout('layouts.app');
    }

    /**
     * Exibe o modal de edição e define o item que será editado.
     *
     * @param \App\Models\Usuario $usuario
     *
     * @return void
     */
    public function edit(Usuario $usuario)
    {
        $this->authorize(Policy::Update->value, $usuario);

        $this->em_edicao = $usuario;

        $this->perfis = Perfil::select('id', 'nome')
                        ->disponiveisParaAtribuicao()
                        ->orderBy('nome', 'asc')
                        ->get();

        $this->exibir_modal_edicao = true;
    }

    /**
     * Atualiza o item em edição no storage.
     *
     * @return void
     */
    public function update()
    {
        abort_if($this->exibir_modal_edicao !== true, 403);

        $this->authorize(Policy::Update->value, $this->em_edicao);

        $this->validate();

        $salvo = $this->em_edicao->updateERevogaDelegacoes();

        $this->flashSelf($salvo);
    }
}
