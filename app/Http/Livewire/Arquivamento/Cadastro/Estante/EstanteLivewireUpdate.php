<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Estante;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Models\Predio;
use App\Models\Andar;
use App\Models\Sala;
use App\Models\Prateleira;
use App\Models\Localidade;
use App\Models\Estante;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class EstanteLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use ComPreferencias;
    use ComExclusao;
    use ComFeedback;
    use ComPaginacao;
    use ComOrdenacao;

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
            'prateleira',
            'apelido',
            'qtd_caixas',
            'acoes',
        ],

        // Quantidade de registros exibidos por página da tabela
        'por_pagina' => 10,
    ];

    /**
     * Item em edição.
     *
     * @var \App\Models\Estante
     */
    public Estante $estante;

    /**
     * Todas as localidades.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $localidades= null;

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
                "unique:estantes,numero,{$this->estante->id},id,sala_id,{$this->estante->sala_id}",
            ],

            'estante.apelido' => [
                'bail',
                'nullable',
                'string',
                'max:100',
                "unique:estantes,apelido,{$this->estante->id},id,sala_id,{$this->estante->sala_id}",
            ],

            'estante.descricao' => [
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

            'estante.sala_id' => [
                'bail',
                'required',
                'integer',
                'exists:salas,id',
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
            'estante.descricao' => __('Descrição'),
            'localidade_id' => __('Localidade'),
            'predio_id' => __('Prédio'),
            'andar_id' => __('Andar'),
            'estante.sala_id' => __('Sala'),
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
        $this->authorize(Policy::ViewOrUpdate->value, Estante::class);
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
        $this->estante = Estante::hierarquia()->findOrFail($id);

        $this->inicializarPropriedadesPais();
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
            ->orderByWhen($this->ordenacoes)
            ->where('estante_id', $this->estante->id)
            ->paginate($this->preferencias['por_pagina']);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.arquivamento.cadastro.estante.edit')->layout('layouts.app');
    }

    /**
     * Executado após a propriedade $localidade_id é atualizada.
     *
     * @return void
     */
    public function updatedLocalidadeId()
    {
        $this->reset(['predio_id', 'predios', 'andar_id', 'andares', 'salas']);
        $this->estante->sala_id = null;

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
        $this->reset(['andar_id', 'andares', 'salas']);
        $this->estante->sala_id = null;

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
        $this->reset(['salas']);
        $this->estante->sala_id = null;

        $this->validateOnly('andar_id');

        $this->salas = $this->salas();
    }

    /**
     * Atualiza o item em edição no storage.
     *
     * @return void
     */
    public function update()
    {
        abort_if($this->modo_edicao !== true, 403);

        $this->authorize(Policy::Update->value, Estante::class);

        $this->validate();

        $salvo = $this->estante->save();

        $this->reset('modo_edicao');

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

    /**
     * Inicializa as propriedades/relacionamentos pai do item em edição.
     *
     * @return void
     */
    private function inicializarPropriedadesPais()
    {
        $this->localidades = Localidade::orderBy('nome', 'asc')->get();
        $this->localidade_id = $this->estante->localidade_id;

        $this->predios = $this->predios();
        $this->predio_id = $this->estante->predio_id;

        $this->andares = $this->andares();
        $this->andar_id = $this->estante->andar_id;

        $this->salas = $this->salas();
    }

    /**
     * Prédios da localidade selecionada.
     *
     * @return \Illuminate\Support\Collection
     */
    private function predios()
    {
        return Predio::where('localidade_id', $this->localidade_id)->orderBy('nome', 'asc')->get();
    }

    /**
     * Andares do prédio atual.
     *
     * @return \Illuminate\Support\Collection
     */
    private function andares()
    {
        return Andar::where('predio_id', $this->predio_id)->orderBy('numero', 'asc')->get();
    }

    /**
     * Salas do andar atual.
     *
     * @return \Illuminate\Support\Collection
     */
    private function salas()
    {
        return Sala::where('andar_id', $this->andar_id)->orderBy('numero', 'asc')->get();
    }
}
