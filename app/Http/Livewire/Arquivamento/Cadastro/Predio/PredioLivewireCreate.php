<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Predio;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Models\Predio;
use App\Models\Localidade;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class PredioLivewireCreate extends Component
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
            'predio',
            'qtd_andares',
            'acoes'
        ],

        // Quantidade de registros exibidos por página da tabela
        'por_pagina' => 10,
    ];

    /**
     * Id do item pai.
     *
     * @var int
     */
    public int $localidade_id;

    /**
     * Item que será criado.
     *
     * @var \App\Models\Predio
     */
    public Predio $predio;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'predio.nome' => [
                'bail',
                'required',
                'string',
                'max:100',
                "unique:predios,nome,null,id,localidade_id,{$this->localidade_id}",
            ],

            'predio.descricao' => [
                'bail',
                'nullable',
                'string',
                'max:255',
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
            'predio.nome' => __('Nome'),
            'predio.descricao' => __('Descrição'),
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
        $this->authorize(Policy::Create->value, Predio::class);
    }

    /**
     * Executado uma única vez, imediatamente após o componente ser
     * instanciado, mas antes do método render() ser acionado. É acionado
     * apenas no carregamento inicial da página e nunca mais chamado, inclusive
     * nas atualizações do componente.
     *
     * @param int $id id do item pai
     *
     * @return void
     */
    public function mount(int $id)
    {
        $this->localidade_id = $id;

        $this->predio = $this->objetoPadrao();
    }

    /**
     * Computed property do item pai.
     *
     * @return \App\Models\Localidade
     */
    public function getLocalidadeProperty()
    {
        return Localidade::hierarquia()->findOrFail($this->localidade_id);
    }

    /**
     * Objeto em branco.
     *
     * @return \App\Models\Predio
     */
    private function objetoPadrao()
    {
        return new Predio();
    }

    /**
     * Computed property para listar de modo paginado os prédios baseado no id
     * da localidade.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPrediosProperty()
    {
        return
        Predio::hierarquia()
            ->where('predios.localidade_id', $this->localidade_id)
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
        return view('livewire.arquivamento.cadastro.predio.create')->layout('layouts.app');
    }

    /**
     * Armazena no storage o item recém criado.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $salvo = $this->localidade->predios()->save($this->predio)
        ? true
        : false;

        $this->predio = $this->objetoPadrao();

        $this->resetPage();

        $this->flashSelf($salvo);
    }

    /**
     * Marca o item para ser excluído e aciona o modal de
     * confirmação.
     *
     * @param \App\Models\Predio $predio
     *
     * @return void
     */
    public function marcarParaExcluir(Predio $predio)
    {
        $this->confirmarExclusao($predio);
    }
}
