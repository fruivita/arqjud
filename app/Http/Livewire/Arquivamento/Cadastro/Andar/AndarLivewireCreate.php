<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Andar;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Models\Andar;
use App\Models\Predio;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class AndarLivewireCreate extends Component
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
            'andar',
            'apelido',
            'qtd_salas',
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
    public int $predio_id;

    /**
     * Item que será criado.
     *
     * @var \App\Models\Andar
     */
    public Andar $andar;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'andar.numero' => [
                'bail',
                'required',
                'integer',
                'between:-100,300',
                "unique:andares,numero,null,id,predio_id,{$this->predio->id}",
            ],

            'andar.apelido' => [
                'bail',
                'nullable',
                'string',
                'max:100',
                "unique:andares,apelido,null,id,predio_id,{$this->predio->id}",
            ],

            'andar.descricao' => [
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
            'andar.numero' => __('Andar'),
            'andar.apelido' => __('Apelido'),
            'andar.descricao' => __('Descrição'),
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
        $this->authorize(Policy::Create->value, Andar::class);
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
        $this->predio_id = $id;

        $this->andar = $this->objetoPadrao();
    }

    /**
     * Computed property do item pai.
     *
     * @return \App\Models\Predio
     */
    public function getPredioProperty()
    {
        return Predio::hierarquia()->findOrFail($this->predio_id);
    }

    /**
     * Objeto em branco.
     *
     * @return \App\Models\Andar
     */
    private function objetoPadrao()
    {
        return new Andar();
    }

    /**
     * Computed property para listar de modo paginado os andares baseado no id
     * do prédio.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAndaresProperty()
    {
        return
        Andar::hierarquia()
            ->where('andares.predio_id', $this->predio->id)
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
        return view('livewire.arquivamento.cadastro.andar.create')->layout('layouts.app');
    }

    /**
     * Armazena no storage o item recém criado.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        $salvo = $this->predio->andares()->save($this->andar)
        ? true
        : false;

        $this->andar = $this->objetoPadrao();

        $this->resetPage();

        $this->flashSelf($salvo);
    }

    /**
     * Marca o item para ser excluído e aciona o modal de
     * confirmação.
     *
     * @param \App\Models\Andar $andar
     *
     * @return void
     */
    public function marcarParaExcluir(Andar $andar)
    {
        $this->confirmarExclusao($andar);
    }
}
