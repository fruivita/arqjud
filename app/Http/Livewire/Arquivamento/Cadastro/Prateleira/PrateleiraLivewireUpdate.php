<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Prateleira;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class PrateleiraLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use ComExclusao;
    use ComFeedback;
    use ComOrdenacao;
    use ComPaginacao;
    use ComPreferencias;

    /**
     * Se o componente deve ser renderizado no modo edição.
     *
     * @var bool
     */
    public bool $modo_edicao = false;

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
     * Item em edição.
     *
     * @var \App\Models\Prateleira
     */
    public Prateleira $prateleira;

    /**
     * Todas as localidades.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $localidades = null;

    /**
     * Id da localidade selecionada.
     *
     * @var int|null
     */
    public $localidade_id = null;

    /**
     * Prédios da localidade selecionada.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $predios = null;

    /**
     * Id do prédio selecionado.
     *
     * @var int|null
     */
    public $predio_id = null;

    /**
     * Andares do prédio selecionado.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $andares = null;

    /**
     * Id do andar selecionado.
     *
     * @var int|null
     */
    public $andar_id = null;

    /**
     * Salas do andar selecionado.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $salas = null;

    /**
     * Id da sala selecionada.
     *
     * @var int|null
     */
    public $sala_id = null;

    /**
     * Estantes da sala selecionada.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $estantes = null;

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
                "unique:prateleiras,numero,{$this->prateleira->id},id,estante_id,{$this->prateleira->estante_id}",
            ],

            'prateleira.apelido' => [
                'bail',
                'nullable',
                'string',
                'max:100',
                "unique:prateleiras,apelido,{$this->prateleira->id},id,estante_id,{$this->prateleira->estante_id}",
            ],

            'prateleira.descricao' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

            'localidade_id' => [
                'bail',
                'required',
                'integer',
                'exists:localidades,id',
            ],

            'predio_id' => [
                'bail',
                'required',
                'integer',
                'exists:predios,id',
            ],

            'andar_id' => [
                'bail',
                'required',
                'integer',
                'exists:andares,id',
            ],

            'sala_id' => [
                'bail',
                'required',
                'integer',
                'exists:salas,id',
            ],

            'prateleira.estante_id' => [
                'bail',
                'required',
                'integer',
                'exists:estantes,id',
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
            'prateleira.descricao' => __('Descrição'),
            'localidade_id' => __('Localidade'),
            'predio_id' => __('Prédio'),
            'andar_id' => __('Andar'),
            'sala_id' => __('Sala'),
            'prateleira.estante_id' => __('Estante'),
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
        $this->authorize(Policy::ViewOrUpdate->value, Prateleira::class);
    }

    /**
     * Executado uma única vez, imediatamente após o componente ser
     * instanciado, mas antes do método render() ser acionado. É acionado
     * apenas no carregamento inicial da página e nunca mais chamado, inclusive
     * nas atualizações do componente.
     *
     * @param int $id id do item em edição
     *
     * @return void
     */
    public function mount(int $id)
    {
        $this->prateleira = Prateleira::hierarquia()->findOrFail($id);

        $this->inicializarPropriedadesPais();
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
            ->orderByWhen($this->ordenacoes)
            ->where('prateleira_id', $this->prateleira->id)
            ->paginate($this->preferencias['por_pagina']);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.arquivamento.cadastro.prateleira.edit')->layout('layouts.app');
    }

    /**
     * Executado após a propriedade $localidade_id é atualizada.
     *
     * @return void
     */
    public function updatedLocalidadeId()
    {
        $this->reset(['predio_id', 'predios', 'andar_id', 'andares', 'sala_id', 'salas', 'estantes']);
        $this->prateleira->estante_id = null;

        $this->validateOnly('localidade_id');

        $this->predios = $this->predios();
    }

    /**
     * Executado após a propriedade $predio_id é atualizada.
     *
     * @return void
     */
    public function updatedPredioId()
    {
        $this->reset(['andar_id', 'andares', 'sala_id', 'salas', 'estantes']);
        $this->prateleira->estante_id = null;

        $this->validateOnly('predio_id');

        $this->andares = $this->andares();
    }

    /**
     * Executado após a propriedade $andar_id é atualizada.
     *
     * @return void
     */
    public function updatedAndarId()
    {
        $this->reset(['sala_id', 'salas', 'estantes']);
        $this->prateleira->estante_id = null;

        $this->validateOnly('andar_id');

        $this->salas = $this->salas();
    }

    /**
     * Executado após a propriedade $sala_id é atualizada.
     *
     * @return void
     */
    public function updatedSalaId()
    {
        $this->reset(['estantes']);
        $this->prateleira->estante_id = null;

        $this->validateOnly('sala_id');

        $this->estantes = $this->estantes();
    }

    /**
     * Atualiza o item em edição no storage.
     *
     * @return void
     */
    public function update()
    {
        abort_if($this->modo_edicao !== true, 403);

        $this->authorize(Policy::Update->value, Prateleira::class);

        $this->validate();

        $salvo = $this->prateleira->save();

        $this->reset('modo_edicao');

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
     * Inicializa as propriedades/relacionamentos pai do item em edição.
     *
     * @return void
     */
    private function inicializarPropriedadesPais()
    {
        $this->localidades = Localidade::ordenacaoPadrao()->get();
        $this->localidade_id = $this->prateleira->localidade_id;

        $this->predios = $this->predios();
        $this->predio_id = $this->prateleira->predio_id;

        $this->andares = $this->andares();
        $this->andar_id = $this->prateleira->andar_id;

        $this->salas = $this->salas();
        $this->sala_id = $this->prateleira->sala_id;

        $this->estantes = $this->estantes();
    }

    /**
     * Prédios da localidade selecionada.
     *
     * @return \Illuminate\Support\Collection
     */
    private function predios()
    {
        return Predio::daLocalidade($this->localidade_id)->ordenacaoPadrao()->get();
    }

    /**
     * Andares do prédio atual.
     *
     * @return \Illuminate\Support\Collection
     */
    private function andares()
    {
        return Andar::doPredio($this->predio_id)->ordenacaoPadrao()->get();
    }

    /**
     * Salas do andar atual.
     *
     * @return \Illuminate\Support\Collection
     */
    private function salas()
    {
        return Sala::doAndar($this->andar_id)->ordenacaoPadrao()->get();
    }

    /**
     * Estantes da sala atual.
     *
     * @return \Illuminate\Support\Collection
     */
    private function estantes()
    {
        return Estante::daSala($this->sala_id)->ordenacaoPadrao()->get();
    }
}
