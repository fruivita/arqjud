<?php

namespace App\Http\Livewire\Administracao\Documentacao;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Models\Documentacao;
use App\Rules\RotaExiste;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class DocumentacaoLivewireCreate extends Component
{
    use AuthorizesRequests;
    use ComPreferencias;
    use ComExclusao;
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
            'app_url',
            'doc_url',
            'acoes',
        ],

        // Quantidade de registros exibidos por página da tabela
        'por_pagina' => 10,
    ];

    /**
     * Item que será criado.
     *
     * @var \App\Models\Documentacao
     */
    public Documentacao $documentacao;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'documentacao.app_link' => [
                'bail',
                'required',
                'string',
                'max:255',
                new RotaExiste(),
                'unique:documentacoes,app_link',
            ],

            'documentacao.doc_link' => [
                'bail',
                'nullable',
                'string',
                'max:255',
                'url',
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
            'documentacao.app_link' => __('Nome da rota'),
            'documentacao.doc_link' => __('Link da documentação'),
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
        $this->authorize(Policy::Create->value, Documentacao::class);
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
        $this->documentacao = $this->objetoPadrao();
    }

    /**
     * Objeto em branco.
     *
     * @return \App\Models\Documentacao
     */
    private function objetoPadrao()
    {
        return new Documentacao();
    }

    /**
     * Computed property para listar de modo paginado as documentações da aplicação.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getDocumentacoesProperty()
    {
        return
        Documentacao::orderByWhen($this->ordenacoes)
            ->paginate($this->preferencias['por_pagina']);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administracao.documentacao.create')->layout('layouts.app');
    }

    /**
     * Armazena no storage o item recém criado.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $salvo = $this->documentacao->save();

        $this->documentacao = $this->objetoPadrao();

        $this->resetPage();

        $this->flashSelf($salvo);
    }

    /**
     * Marca o item para ser excluído e aciona o modal de
     * confirmação.
     *
     * @param \App\Models\Documentacao $documentacao
     *
     * @return void
     */
    public function marcarParaExcluir(Documentacao $documentacao)
    {
        $this->confirmarExclusao($documentacao);
    }
}
