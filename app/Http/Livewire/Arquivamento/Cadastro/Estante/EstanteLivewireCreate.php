<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Estante;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Models\Estante;
use App\Models\Sala;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class EstanteLivewireCreate extends Component
{
    use AuthorizesRequests;
    use ComExclusao;
    use ComFeedback;
    use ComOrdenacao;
    use ComPaginacao;
    use ComPreferencias;

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
            'acoes',
        ],

        // Quantidade de registros exibidos por página da tabela
        'por_pagina' => 10,
    ];

    /**
     * Id do item pai.
     *
     * @var int
     */
    public int $sala_id;

    /**
     * Item que será criado.
     *
     * @var \App\Models\Estante
     */
    public Estante $estante;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'estante.numero' => [
                'bail',
                'required',
                'integer',
                'between:1,100000',
                "unique:estantes,numero,null,id,sala_id,{$this->sala_id}",
            ],

            'estante.apelido' => [
                'bail',
                'nullable',
                'string',
                'max:100',
                "unique:estantes,apelido,null,id,sala_id,{$this->sala_id}",
            ],

            'estante.descricao' => [
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
            'estante.numero' => __('Estante'),
            'estante.apelido' => __('Apelido'),
            'estante.descricao' => __('Descrição'),
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
        $this->authorize(Policy::Create->value, Estante::class);
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
        $this->sala_id = $id;

        $this->estante = $this->objetoPadrao();
    }

    /**
     * Computed property do item pai.
     *
     * @return \App\Models\Sala
     */
    public function getSalaProperty()
    {
        return Sala::hierarquia()->findOrFail($this->sala_id);
    }

    /**
     * Objeto em branco.
     *
     * @return \App\Models\Estante
     */
    private function objetoPadrao()
    {
        return new Estante();
    }

    /**
     * Computed property para listar de modo paginado as estantes baseado no id
     * da sala.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getEstantesProperty()
    {
        return
        Estante::hierarquia()
            ->where('estantes.sala_id', $this->sala_id)
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
        return view('livewire.arquivamento.cadastro.estante.create')->layout('layouts.app');
    }

    /**
     * Armazena no storage o item recém criado.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $salvo = $this->sala->criarEstante($this->estante)
        ? true
        : false;

        $this->estante = $this->objetoPadrao();

        $this->resetPage();

        $this->flashSelf($salvo);
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
