<?php

namespace App\Http\Livewire\Autorizacao\Perfil;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Http\Livewire\Traits\ComAcoesDeCheckbox;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Models\Permissao;
use App\Models\Perfil;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class PerfilLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use ComPreferencias;
    use ComAcoesDeCheckbox;
    use ComFeedback;
    use ComPaginacao;
    use ComOrdenacao;

    /**
     * Preferências do usuário.
     *
     * @var array<string, mixed>
     */
    public array $preferencias = [
        // Nome das colunas da tabela que podem ser ocultadas
        'colunas' => [
            'seletores',
            'permissao',
            'descricao',
            'acoes',
        ],

        // Quantidade de registros exibidos por página da tabela
        'por_pagina' => 10,
    ];

    /**
     * Se o componente deve ser renderizado no modo edição.
     *
     * @var bool
     */
    public bool $modo_edicao = false;

    /**
     * Item em edição.
     *
     * @var \App\Models\Perfil
     */
    public Perfil $perfil;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'perfil.nome' => [
                'bail',
                'required',
                'string',
                'max:50',
                "unique:perfis,nome,{$this->perfil->id}",
            ],

            'perfil.descricao' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

            'selecionados' => [
                'bail',
                'nullable',
                'array',
                'exists:permissoes,id',
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
            'perfil.nome' => __('Nome'),
            'perfil.descricao' => __('Descrição'),
            'selecionados' => __('Permissão'),
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
        $this->authorize(Policy::ViewOrUpdate->value, Perfil::class);
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
        $this->perfil->load(['permissoes' => function ($query) {
            $query->select('id');
        }]);
    }

    /**
     * Computed property para listar de modo paginado as permissões.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPermissoesProperty()
    {
        return
        Permissao::orderByWhen($this->ordenacoes)
            ->paginate($this->preferencias['por_pagina']);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.autorizacao.perfil.edit')->layout('layouts.app');
    }

    /**
     * Atualiza o item em edição no storage.
     *
     * @return void
     */
    public function update()
    {
        abort_if($this->modo_edicao !== true, 403);

        $this->authorize(Policy::Update->value, Perfil::class);

        $this->validate();

        $salvo = $this->perfil->salvaESincronizaPermissoes($this->selecionados);

        $this->reset('modo_edicao');

        $this->flashSelf($salvo);
    }

    /**
     * Reseta a propriedade `$acao_checkbox` se houver nageção entre páginas,
     * isto é, se o usuário navegar para outra página.
     *
     * Útil para que o usuário possa definir o comportamento desejado na página
     * seguinte.
     *
     * @return void
     */
    public function updatedPaginators()
    {
        $this->reset('acao_checkbox');
    }

    /**
     * Todas as linhas (checkbox ids) que devem ser marcados no carregamento
     * inicial (mount) da página.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function selecionarIds()
    {
        return $this->perfil->permissoes;
    }

    /**
     * Todas as linhas (checkbox ids) disponíveis para seleção.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function todosIdsSelecionaveis()
    {
        return Permissao::select('id')->get();
    }

    /**
     * Range de linhas (checkbox ids) disponíveis para seleção. Em regra, as
     * linhas atualmente exibidas na página.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function idsAtualmenteSelecionaveis()
    {
        return $this->permissoes;
    }
}
