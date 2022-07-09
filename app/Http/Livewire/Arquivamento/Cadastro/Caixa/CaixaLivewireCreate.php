<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Caixa;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Models\Caixa;
use App\Models\Prateleira;
use App\Models\VolumeCaixa;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class CaixaLivewireCreate extends Component
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
            'caixa',
            'ano',
            'qtd_volumes',
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
    public int $prateleira_id;

    /**
     * Item que será criado.
     *
     * @var \App\Models\Caixa
     */
    public Caixa $caixa;

    /**
     * Quantidade de caixas que serão criadas por vez.
     *
     * @var int
     */
    public $quantidade = 1;

    /**
     * Número de volumes que a(s) caixa(s) terá(âo).
     *
     * @var int
     */
    public $volumes = 1;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'quantidade' => [
                'bail',
                'required',
                'integer',
                'between:1,1000',
            ],

            'caixa.ano' => [
                'bail',
                'required',
                'integer',
                'between:1900,' . now()->format('Y'),
            ],

            'caixa.numero' => [
                'bail',
                'required',
                'integer',
                'min:1',
                "unique:caixas,numero,null,id,ano,{$this->caixa->ano}",
            ],

            'caixa.descricao' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

            'volumes' => [
                'bail',
                'required',
                'integer',
                'between:1,1000',
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
            'quantidade' => __('Quantidade'),
            'caixa.ano' => __('Ano'),
            'caixa.numero' => __('Número'),
            'caixa.descricao' => __('Descrição'),
            'volumes' => __('Volumes'),
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
        $this->authorize(Policy::Create->value, Caixa::class);
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
        $this->prateleira_id = $id;

        $this->caixa = $this->objetoPadrao();
    }

    /**
     * Computed property do item pai.
     *
     * @return \App\Models\Prateleira
     */
    public function getPrateleiraProperty()
    {
        return Prateleira::hierarquia()->findOrFail($this->prateleira_id);
    }

    /**
     * Objeto em branco.
     *
     * @return \App\Models\Caixa
     */
    private function objetoPadrao()
    {
        return new Caixa();
    }

    /**
     * Computed property para listar de modo paginado as caixas baseado no id
     * da prateleira.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCaixasProperty()
    {
        return
        Caixa::hierarquia()
            ->where('caixas.prateleira_id', $this->prateleira_id)
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
        return view('livewire.arquivamento.cadastro.caixa.create')->layout('layouts.app');
    }

    /**
     * Executado após a propriedade $caixa.ano ser atualizada.
     *
     * @return void
     */
    public function updatedCaixaAno()
    {
        $this->validateOnly('caixa.ano');

        $this->caixa->numero = Caixa::proximoNumeroCaixa($this->caixa->ano);
    }

    /**
     * Armazena no storage o item recém criado.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $salvo = Caixa::criarMuitas(
            $this->caixa,
            $this->quantidade(),
            $this->volumes(),
            $this->prateleira,
        );

        $this->caixa = $this->objetoPadrao();

        $this->resetPage();

        $this->flashSelf($salvo);
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

    /**
     * Quantidade de caixas que serão criadas.
     *
     * Leva em consideração a permissão do usuário.
     *
     * @return int `1` se o usuário não possuir permissão para criar múltiplas
     *             caixas ou o valor definido em `$quantidade`
     */
    private function quantidade()
    {
        return auth()->user()->can(Policy::CreateMany->value, Caixa::class)
        ? $this->quantidade
        : 1;
    }

    /**
     * Quantidade de volumes da caixa que será criada.
     *
     * Leva em consideração a permissão do usuário.
     *
     * @return int `1` se o usuário não possuir permissão para criar múltiplos
     *             volumes de caixa ou o valor definido em `$volumes`
     */
    private function volumes()
    {
        return auth()->user()->can(Policy::Create->value, VolumeCaixa::class)
        ? $this->volumes
        : 1;
    }
}
