<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Prateleira;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Models\Prateleira;
use App\Models\Estante;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class PrateleiraLivewireCreate extends Component
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
            'prateleira',
            'apelido',
            'qtd_caixas',
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
    public int $estante_id;

    /**
     * Item que será criado.
     *
     * @var \App\Models\Prateleira
     */
    public Prateleira $prateleira;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'prateleira.numero' => [
                'bail',
                'required',
                'integer',
                'between:1,100000',
                "unique:prateleiras,numero,null,id,estante_id,{$this->estante_id}",
            ],

            'prateleira.apelido' => [
                'bail',
                'nullable',
                'string',
                'max:100',
                "unique:prateleiras,apelido,null,id,estante_id,{$this->estante_id}",
            ],

            'prateleira.descricao' => [
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
            'prateleira.numero' => __('Prateleira'),
            'prateleira.apelido' => __('Apelido'),
            'prateleira.descricao' => __('Descrição'),
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
        $this->authorize(Policy::Create->value, Prateleira::class);
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
        $this->estante_id = $id;

        $this->prateleira = $this->objetoPadrao();
    }

    /**
     * Computed property do item pai.
     *
     * @return \App\Models\Estante
     */
    public function getEstanteProperty()
    {
        return Estante::hierarquia()->findOrFail($this->estante_id);
    }

    /**
     * Objeto em branco.
     *
     * @return \App\Models\Prateleira
     */
    private function objetoPadrao()
    {
        return new Prateleira();
    }

    /**
     * Computed property para listar de modo paginado as prateleiras baseado no
     * id da estante.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPrateleirasProperty()
    {
        return
        Prateleira::hierarquia()
            ->where('prateleiras.estante_id', $this->estante_id)
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
        return view('livewire.arquivamento.cadastro.prateleira.create')->layout('layouts.app');
    }

    /**
     * Armazena no storage o item recém criado.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $salvo = $this->estante->prateleiras()->save($this->prateleira)
        ? true
        : false;

        $this->prateleira = $this->objetoPadrao();

        $this->resetPage();

        $this->flashSelf($salvo);
    }

    /**
     * Marca o item para ser excluído e aciona o modal de
     * confirmação.
     *
     * @param \App\Models\Prateleira $prateleira
     *
     * @return void
     */
    public function marcarParaExcluir(Prateleira $prateleira)
    {
        $this->confirmarExclusao($prateleira);
    }
}
