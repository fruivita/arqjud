<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Sala;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Models\Andar;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Predio;
use App\Models\Sala;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class SalaLivewireUpdate extends Component
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
            'estante',
            'apelido',
            'qtd_prateleiras',
            'acoes',
        ],

        // Quantidade de registros exibidos por página da tabela
        'por_pagina' => 10,
    ];

    /**
     * Item em edição.
     *
     * @var \App\Models\Sala
     */
    public Sala $sala;

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
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'sala.numero' => [
                'bail',
                'required',
                'string',
                'max:50',
                "unique:salas,numero,{$this->sala->id},id,andar_id,{$this->sala->andar_id}",
            ],

            'sala.descricao' => [
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

            'sala.andar_id' => [
                'bail',
                'required',
                'integer',
                'exists:andares,id',
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
            'sala.numero' => __('Sala'),
            'sala.descricao' => __('Descrição'),
            'localidade_id' => __('Localidade'),
            'predio_id' => __('Prédio'),
            'sala.andar_id' => __('Andar'),
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
        $this->authorize(Policy::ViewOrUpdate->value, Sala::class);
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
        $this->sala = Sala::hierarquia()->findOrFail($id);

        $this->inicializarPropriedadesPais();
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
            ->orderByWhen($this->ordenacoes)
            ->where('sala_id', $this->sala->id)
            ->paginate($this->preferencias['por_pagina']);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.arquivamento.cadastro.sala.edit')->layout('layouts.app');
    }

    /**
     * Executado após a propriedade $localidade_id é atualizada.
     *
     * @return void
     */
    public function updatedLocalidadeId()
    {
        $this->reset(['predio_id', 'predios', 'andares']);
        $this->sala->andar_id = null;

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
        $this->reset(['andares']);
        $this->sala->andar_id = null;

        $this->validateOnly('predio_id');

        $this->andares = $this->andares();
    }

    /**
     * Atualiza o item em edição no storage.
     *
     * @return void
     */
    public function update()
    {
        abort_if($this->modo_edicao !== true, 403);

        $this->authorize(Policy::Update->value, Sala::class);

        $this->validate();

        $salvo = $this->sala->save();

        $this->reset('modo_edicao');

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

    /**
     * Inicializa as propriedades/relacionamentos pai do item em edição.
     *
     * @return void
     */
    private function inicializarPropriedadesPais()
    {
        $this->localidades = Localidade::ordenacaoPadrao()->get();
        $this->localidade_id = $this->sala->localidade_id;

        $this->predios = $this->predios();
        $this->predio_id = $this->sala->predio_id;

        $this->andares = $this->andares();
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
}
